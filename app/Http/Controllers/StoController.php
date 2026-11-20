<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoRequest;
use App\Models\{ImportBatch, Sto, StoActivity, StoItem};
use App\Services\StoPdfParser;
use App\Support\{ImportCleanup, XlsxWriter};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Cache, DB, Storage};
use Illuminate\Validation\ValidationException;

class StoController extends Controller
{
    /* ------------------------------------------------------------------
     |  Daftar (Read)
     * ------------------------------------------------------------------ */

    /** Parameter filter yang dibawa ke tautan (Unduh Excel, isi cepat, paginasi). */
    public const FILTER_KEYS = ['q', 'activity', 'completeness', 'dari', 'sampai', 'urut'];

    public const SORTS = [
        'tanggal_desc' => 'Tanggal STO terbaru',
        'tanggal_asc' => 'Tanggal STO terlama',
        'input_desc' => 'Terakhir diinput',
        'nomor_asc' => 'Nomor STO (A–Z)',
        'total_desc' => 'Total terbesar',
    ];

    private static function validDate(?string $value): ?string
    {
        return is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) && checkdate((int) substr($value, 5, 2), (int) substr($value, 8, 2), (int) substr($value, 0, 4))
            ? $value : null;
    }

    private function filtered(Request $r): Builder
    {
        $query = Sto::with(['items', 'batch']);

        if ($r->filled('q')) {
            $s = substr(trim((string) $r->q), 0, 100);
            $query->where(function ($q) use ($s) {
                $q->where('number', 'like', "%$s%")
                    ->orWhere('origin', 'like', "%$s%")
                    ->orWhere('destination', 'like', "%$s%")
                    ->orWhere('soj', 'like', "%$s%")
                    ->orWhere('mmj', 'like', "%$s%")
                    ->orWhereHas('items', fn ($i) => $i->where('name', 'like', "%$s%"));
            });
        }
        if (in_array($r->activity, ['BONGKAR', 'MUAT'], true)) {
            $query->where('activity', $r->activity);
        }
        if ($r->completeness === 'incomplete') {
            $query->incomplete();
        } elseif ($r->completeness === 'complete') {
            $query->complete();
        }
        if ($from = self::validDate($r->query('dari'))) {
            $query->whereDate('document_date', '>=', $from);
        }
        if ($to = self::validDate($r->query('sampai'))) {
            $query->whereDate('document_date', '<=', $to);
        }

        match (array_key_exists((string) $r->query('urut'), self::SORTS) ? $r->query('urut') : 'tanggal_desc') {
            'tanggal_asc' => $query->orderBy('document_date')->orderBy('id'),
            'input_desc' => $query->orderByDesc('created_at')->orderByDesc('id'),
            'nomor_asc' => $query->orderBy('number'),
            'total_desc' => $query->orderByDesc('total')->orderByDesc('id'),
            default => $query->orderByDesc('document_date')->orderByDesc('id'),
        };

        return $query;
    }

    /** Data untuk halaman Master STO (juga dipakai saat pratinjau impor tampil sebagai modal). */
    private function indexData(Request $r): array
    {
        $quick = $r->query('mode') === 'isi';
        $stos = $this->filtered($r)
            ->paginate($quick ? 50 : 20)
            ->withPath(route('sto.index'))
            ->appends($r->except('open', 'unggah', 'tambah'));

        // Detail STO dimuat saat diklik (lihat panel()). Yang dirender langsung hanya STO yang
        // memang harus terbuka: ?open=ID, dibuka ulang setelah simpan, atau gagal validasi.
        $reopen = (string) session('reopen');
        $wanted = array_filter([
            $r->query('open'),
            str_starts_with($reopen, 'sto-') ? substr($reopen, 4) : null,
            str_starts_with((string) old('_modal'), 'sto-') ? substr((string) old('_modal'), 4) : null,
        ], fn ($id) => ctype_digit((string) $id));
        $dialogStos = $wanted
            ? Sto::with(['items', 'batch', 'activities', 'creator', 'updater'])->whereKey(array_unique($wanted))->get()
            : collect();

        return [
            'stos' => $stos,
            'quick' => $quick,
            'dialogStos' => $dialogStos,
            'incompleteCount' => Sto::incomplete()->count(),
            'failedRows' => ImportBatch::pendingFailedFor($r->user()),
            'duplicatePdf' => session('pdf_duplicate'),
            'sorts' => self::SORTS,
        ];
    }

    /** Potongan HTML jendela detail satu STO, dimuat saat nomor STO diklik. */
    public function panel(Sto $sto)
    {
        $sto->load(['items', 'batch', 'activities', 'creator', 'updater']);

        return response()->view('sto._modal', ['sto' => $sto])->header('Cache-Control', 'no-store');
    }

    public function index(Request $r)
    {
        ImportCleanup::runIfDue();

        return view('sto.index', $this->indexData($r));
    }

    /** Detail kini berupa modal pada halaman Master STO; alamat lama diteruskan ke sana. */
    public function show(Sto $sto)
    {
        return redirect()->route('sto.index', ['open' => $sto->id]);
    }

    /* ------------------------------------------------------------------
     |  Impor PDF (pratinjau, simpan, batal)
     * ------------------------------------------------------------------ */

    public function upload(Request $r, StoPdfParser $parser)
    {
        $r->validate(['pdf' => 'required|file|mimes:pdf|extensions:pdf|max:10240']);
        ImportCleanup::runIfDue();

        $file = $r->file('pdf');
        $sha = hash_file('sha256', $file->getRealPath());
        $name = $file->getClientOriginalName();

        // PDF yang sama masih dalam pratinjau milik pengguna ini: buka pratinjau itu saja.
        $open = ImportBatch::where('user_id', $r->user()->id)->where('sha256', $sha)
            ->where('status', 'preview')->where('expires_at', '>', now())->latest()->first();
        if ($open) {
            return redirect()->route('sto.preview', $open)->with('success', 'PDF ini sudah dibaca dan masih dalam pratinjau.');
        }

        $path = $file->store('sto', 'local');

        // PDF yang sama pernah diimpor: tahan dulu, tanyakan apakah tetap ingin dibaca.
        $done = ImportBatch::with('user')->where('sha256', $sha)->where('status', 'committed')->latest()->first();
        if ($done) {
            session()->put('pdf_duplicate', [
                'path' => $path, 'name' => $name, 'sha' => $sha,
                'imported_at' => $done->updated_at->format('d/m/Y H:i'),
                'by' => $done->user?->name,
                'original' => $done->original_name,
                'count' => $done->stos()->withTrashed()->count(),
            ]);

            return redirect()->route('sto.index', ['unggah' => 1]);
        }

        return $this->readPdf($r, $parser, $path, $name, $sha);
    }

    /** Lanjutkan membaca PDF yang ditahan karena pernah diimpor. */
    public function uploadAnyway(Request $r, StoPdfParser $parser)
    {
        $held = session()->pull('pdf_duplicate');
        abort_unless(is_array($held) && str_starts_with((string) ($held['path'] ?? ''), 'sto/') && Storage::exists($held['path']), 410, 'Unggahan sudah tidak tersedia. Pilih PDF lagi.');

        return $this->readPdf($r, $parser, $held['path'], $held['name'], $held['sha']);
    }

    /** Batal membaca PDF yang ditahan: hapus filenya. */
    public function uploadDiscard()
    {
        $held = session()->pull('pdf_duplicate');
        if (is_array($held) && str_starts_with((string) ($held['path'] ?? ''), 'sto/')) {
            Storage::delete($held['path']);
        }

        return redirect()->route('sto.index');
    }

    private function readPdf(Request $r, StoPdfParser $parser, string $path, string $name, string $sha)
    {
        try {
            $result = $parser->parse(Storage::path($path));
        } catch (\Throwable $e) {
            Storage::delete($path);
            report($e);

            return redirect()->route('sto.index', ['unggah' => 1])->withErrors([
                'pdf' => $e instanceof \RuntimeException ? $e->getMessage() : 'PDF tidak bisa dibuka. Pastikan file tidak rusak atau terkunci.',
            ]);
        }
        try {
            $batch = ImportBatch::create([
                'user_id' => $r->user()->id,
                'original_name' => $name,
                'path' => $path,
                'sha256' => $sha,
                'payload' => $result['rows'],
                'warnings' => $result['warnings'],
                'failed' => $result['failed'] ?? [],
                'expires_at' => now()->addHours(24),
            ]);
        } catch (\Throwable $e) {
            Storage::delete($path);
            throw $e;
        }

        return redirect()->route('sto.preview', $batch);
    }

    private function own(Request $r, ImportBatch $batch): void
    {
        abort_unless((int) $batch->user_id === (int) $r->user()->id, 403);
    }

    public function preview(Request $r, ImportBatch $batch)
    {
        $this->own($r, $batch);
        abort_unless($batch->status === 'preview' && $batch->expires_at->isFuture(), 410, 'Pratinjau tidak berlaku.');

        $seen = [];
        $numbers = array_column($batch->payload, 'number');
        $existing = Sto::whereIn('number', $numbers)->pluck('number')->all();
        $trashed = Sto::onlyTrashed()->whereIn('number', $numbers)->pluck('number')->all();
        $rows = array_map(function ($row) use (&$seen, $existing, $trashed) {
            $row['trashed'] = in_array($row['number'], $trashed, true);
            $row['duplicate'] = isset($seen[$row['number']]) || $row['trashed'] || in_array($row['number'], $existing, true);
            $seen[$row['number']] = true;

            return $row;
        }, $batch->payload);

        // Pratinjau tampil sebagai modal di atas halaman Master STO.
        return view('sto.index', $this->indexData($r) + ['batch' => $batch, 'rows' => $rows]);
    }

    public function commit(Request $r, ImportBatch $batch)
    {
        $this->own($r, $batch);

        $userId = $r->user()->id;

        return Cache::lock('sto-import-global', 120)->block(5, function () use ($batch, $userId) {
            $counts = DB::transaction(function () use ($batch, $userId) {
                $b = ImportBatch::whereKey($batch->id)->lockForUpdate()->firstOrFail();
                abort_unless($b->status === 'preview' && $b->expires_at->isFuture(), 410, 'Pratinjau tidak berlaku.');
                $saved = 0;
                $skip = 0;
                foreach ($b->payload as $row) {
                    // STO di Sampah juga dihitung sudah ada (nomor STO unik).
                    if (Sto::withTrashed()->where('number', $row['number'])->exists()) {
                        $skip++;
                        continue;
                    }
                    $items = $row['items'];
                    unset($row['items'], $row['notes']);
                    $sto = Sto::create($row + ['import_batch_id' => $b->id, 'created_by' => $userId, 'updated_by' => $userId]);
                    $sto->items()->createMany($items);
                    StoActivity::record($sto, 'imported', [], $b->original_name.' · halaman '.$row['source_page']);
                    $saved++;
                }
                $b->update(['status' => 'committed', 'payload' => []]);

                return [$saved, $skip, count($b->failed ?? [])];
            });

            $message = "{$counts[0]} STO disimpan.".($counts[1] ? " {$counts[1]} duplikat dilewati." : '')
                .($counts[2] ? " {$counts[2]} STO gagal dibaca bisa ditambahkan manual." : '');

            return redirect()->route('sto.index')->with('success', $message);
        });
    }

    /** PDF sumber sebuah impor, untuk membuka halaman STO yang gagal dibaca. Hanya pengunggahnya. */
    public function batchPdf(Request $r, ImportBatch $batch)
    {
        $this->own($r, $batch);
        abort_unless(Storage::exists($batch->path), 404);

        return response()->file(Storage::path($batch->path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="impor-'.$batch->id.'.pdf"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /** Sembunyikan daftar STO gagal-baca dari satu impor (sudah ditangani atau tidak diperlukan). */
    public function dismissFailed(Request $r, ImportBatch $batch)
    {
        $this->own($r, $batch);
        $batch->update(['failed' => null]);

        return back()->with('success', 'Daftar STO gagal dibaca dari '.$batch->original_name.' disembunyikan.');
    }

    public function cancel(Request $r, ImportBatch $batch)
    {
        $this->own($r, $batch);
        DB::transaction(function () use ($batch) {
            $b = ImportBatch::whereKey($batch->id)->lockForUpdate()->firstOrFail();
            abort_unless($b->status === 'preview', 409);
            Storage::delete($b->path);
            $b->delete();
        });

        return redirect()->route('sto.index')->with('success', 'Impor dibatalkan.');
    }

    /* ------------------------------------------------------------------
     |  Tambah, ubah, hapus (Create, Update, Delete)
     * ------------------------------------------------------------------ */

    public function store(StoRequest $request)
    {
        $sto = DB::transaction(function () use ($request) {
            $data = $request->validated();
            $source = [];
            if (! empty($data['import_batch_id'])) {
                $source = ['import_batch_id' => (int) $data['import_batch_id'], 'source_page' => $data['source_page'] ?? null];
            }
            $sto = $this->persist(new Sto(['created_by' => $request->user()->id] + $source), $data, $request->user()->id);
            StoActivity::record($sto, 'created', [], $source ? 'Dari STO gagal dibaca · halaman '.($source['source_page'] ?? '-') : null);

            return $sto;
        });

        return redirect()->route('sto.index')->with([
            'success' => "STO {$sto->number} ditambahkan.",
            'reopen' => 'sto-'.$sto->id,
        ]);
    }

    public function update(StoRequest $request, Sto $sto)
    {
        $saved = DB::transaction(function () use ($request, $sto) {
            $locked = Sto::whereKey($sto->id)->lockForUpdate()->firstOrFail();
            $before = $locked->snapshot();
            $saved = $this->persist($locked, $request->validated(), $request->user()->id);
            StoActivity::record($saved, 'updated', Sto::diff($before, $saved->fresh('items')->snapshot()));

            return $saved;
        });

        return back()->with([
            'success' => "STO {$saved->number} diperbarui.",
            'reopen' => 'sto-'.$saved->id,
        ]);
    }

    /** Pengisian cepat SOJ dan MMJ dari modal detail. */
    public function references(Request $r, Sto $sto)
    {
        $data = $r->validateWithBag('sto', [
            'soj' => ['nullable', 'string', 'max:100'],
            'mmj' => ['nullable', 'string', 'max:100'],
        ]);

        DB::transaction(function () use ($sto, $data, $r) {
            $before = $sto->snapshot();
            $sto->fill([
                'soj' => $this->nullable($data['soj'] ?? null),
                'mmj' => $this->nullable($data['mmj'] ?? null),
            ]);
            if ($sto->isDirty()) {
                $sto->updated_by = $r->user()->id;
                $sto->save();
                StoActivity::record($sto, 'references', Sto::diff($before, $sto->snapshot()));
            }
        });

        return back()->with([
            'success' => "SOJ/MMJ STO {$sto->number} disimpan.",
            'reopen' => 'sto-'.$sto->id,
        ]);
    }

    /**
     * Isi cepat SOJ/MMJ untuk banyak STO sekaligus (mode isi cepat di Master STO).
     * Setiap baris membawa nilai awal (saat halaman dibuka). Hanya kolom yang diubah pengguna
     * yang disimpan; bila di database nilainya sudah berubah oleh orang lain, baris itu dilewati.
     */
    public function bulkReferences(Request $r)
    {
        $data = $r->validateWithBag('quick', [
            'refs' => ['required', 'array', 'max:100'],
            'refs.*.soj' => ['nullable', 'string', 'max:100'],
            'refs.*.mmj' => ['nullable', 'string', 'max:100'],
            'refs.*.soj_awal' => ['nullable', 'string', 'max:100'],
            'refs.*.mmj_awal' => ['nullable', 'string', 'max:100'],
        ]);

        $saved = 0;
        $conflicts = [];
        DB::transaction(function () use ($data, $r, &$saved, &$conflicts) {
            $ids = array_filter(array_keys($data['refs']), fn ($id) => ctype_digit((string) $id));
            $stos = Sto::whereKey($ids)->lockForUpdate()->get()->keyBy('id');
            foreach ($data['refs'] as $id => $row) {
                $sto = $stos->get((int) $id);
                if (! $sto) {
                    continue;
                }
                $before = $sto->snapshot();
                $conflict = false;
                foreach (['soj', 'mmj'] as $field) {
                    $new = $this->nullable($row[$field] ?? null);
                    $old = $this->nullable($row[$field.'_awal'] ?? null);
                    if ($new === $old) {
                        continue; // tidak diubah pengguna
                    }
                    if ($this->nullable($sto->{$field}) !== $old) {
                        $conflict = true; // sudah diubah orang lain sejak halaman dibuka
                        continue;
                    }
                    $sto->{$field} = $new;
                }
                if ($conflict) {
                    $conflicts[] = $sto->number;
                }
                if ($sto->isDirty()) {
                    $sto->updated_by = $r->user()->id;
                    $sto->save();
                    StoActivity::record($sto, 'references', Sto::diff($before, $sto->snapshot()), 'Isi cepat');
                    $saved++;
                }
            }
        });

        $message = $saved ? "SOJ/MMJ {$saved} STO disimpan." : 'Tidak ada perubahan SOJ/MMJ.';
        if ($conflicts) {
            $message .= ' '.count($conflicts).' STO dilewati karena sudah diubah orang lain: '.implode(', ', array_slice($conflicts, 0, 5)).'. Periksa lagi.';
        }

        return back()->with('success', $message);
    }

    /** Hapus = pindah ke Sampah (bisa dipulihkan). */
    public function destroy(Request $r, Sto $sto)
    {
        DB::transaction(function () use ($sto, $r) {
            $locked = Sto::whereKey($sto->id)->lockForUpdate()->firstOrFail();
            $locked->deleted_by = $r->user()->id;
            $locked->save();
            $locked->delete();
            StoActivity::record($locked, 'deleted');
        });

        return redirect()->route('sto.index')->with([
            'success' => "STO {$sto->number} dipindah ke Sampah.",
            'undo' => ['label' => 'Urungkan', 'action' => route('sto.restore', $sto->id)],
        ]);
    }

    /* ------------------------------------------------------------------
     |  Sampah: pulihkan atau hapus permanen
     * ------------------------------------------------------------------ */

    public function trash()
    {
        $stos = Sto::onlyTrashed()->with(['deleter', 'items'])
            ->latest('deleted_at')->orderByDesc('id')
            ->paginate(20);

        return view('sto.trash', ['stos' => $stos]);
    }

    public function restore(Sto $sto)
    {
        abort_unless($sto->trashed(), 404);
        DB::transaction(function () use ($sto) {
            $sto->deleted_by = null;
            $sto->restore();
            StoActivity::record($sto, 'restored');
        });

        return redirect()->route('sto.index', ['open' => $sto->id])->with('success', "STO {$sto->number} dipulihkan.");
    }

    public function forceDestroy(Sto $sto)
    {
        abort_unless($sto->trashed(), 404);
        $number = $sto->number;
        DB::transaction(function () use ($sto) {
            StoActivity::record($sto, 'purged');
            $sto->forceDelete(); // rincian barang ikut terhapus; riwayat tetap ada (sto_id menjadi kosong)
        });

        return redirect()->route('sto.trash')->with('success', "STO {$number} dihapus permanen.");
    }

    /** Menyimpan STO beserta rincian barangnya; biaya dan total dihitung di server. */
    private function persist(Sto $sto, array $data, ?int $userId = null): Sto
    {
        $lines = [];
        $total = 0;
        foreach (array_values($data['items']) as $i => $item) {
            // Hitung dengan bilangan bulat (qty per 0,001; tarif per sen) agar tidak ada selisih pembulatan desimal.
            $milli = (int) round(((float) $item['quantity']) * 1000);
            $cents = (int) round(((float) $item['rate']) * 100);
            $amount = intdiv($milli * $cents + 500, 1000);
            $total += $amount;
            $lines[] = [
                'line' => $i + 1,
                'name' => trim($item['name']),
                'quantity' => $this->fixed($milli, 3),
                'unit' => mb_strtoupper(trim($item['unit'])),
                'rate' => $this->fixed($cents, 2),
                'amount' => $this->fixed($amount, 2),
            ];
        }

        $sto->fill([
            'number' => trim($data['number']),
            'document_date' => $data['document_date'],
            'activity' => $data['activity'],
            'origin' => trim($data['origin']),
            'destination' => trim($data['destination']),
            'soj' => $this->nullable($data['soj'] ?? null),
            'mmj' => $this->nullable($data['mmj'] ?? null),
            'total' => $this->fixed($total, 2),
            'updated_by' => $userId,
        ])->save();

        StoItem::where('sto_id', $sto->id)->delete();
        $sto->items()->createMany($lines);

        return $sto;
    }

    private function fixed(int $value, int $decimals): string
    {
        $base = 10 ** $decimals;

        return sprintf('%d.%0'.$decimals.'d', intdiv($value, $base), $value % $base);
    }

    private function nullable(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /* ------------------------------------------------------------------
     |  PDF dan laporan
     * ------------------------------------------------------------------ */

    public function pdf(Sto $sto)
    {
        abort_unless($sto->batch && Storage::exists($sto->batch->path), 404);

        return response()->file(Storage::path($sto->batch->path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="sto-'.$sto->number.'.pdf"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Unduh Excel (.xlsx) sesuai filter yang sedang aktif di Master STO.
     * Angka tersimpan sebagai angka asli, jadi terbaca benar di Excel pengaturan apa pun.
     * Total STO hanya ditulis pada baris barang pertama agar kolom bisa dijumlah tanpa ganda.
     */
    public function export(Request $r)
    {
        $query = $this->filtered($r)->with(['items']);
        $name = 'sto-alat-berat-w1-'.now()->format('Ymd-Hi');

        if (! class_exists(\ZipArchive::class)) {
            return $this->exportCsv($query, $name.'.csv');
        }

        $xlsx = new XlsxWriter([
            ['Nomor STO', 'text', 15], ['Tanggal', 'date', 11], ['Kegiatan', 'text', 10],
            ['Asal', 'text', 34], ['Tujuan', 'text', 34], ['SOJ', 'text', 18], ['MMJ', 'text', 18],
            ['No', 'int', 5], ['Nama Barang', 'text', 30], ['Qty', 'qty', 11], ['Satuan', 'text', 8],
            ['Tarif', 'money', 11], ['Biaya', 'money', 14], ['Total STO', 'money', 15],
        ]);
        $query->chunk(200, function ($stos) use ($xlsx) {
            foreach ($stos as $s) {
                foreach ($s->items->values() as $n => $i) {
                    $xlsx->addRow([
                        $s->number, $s->document_date->format('Y-m-d'), $s->activity,
                        $this->oneLine($s->origin), $this->oneLine($s->destination), $s->soj, $s->mmj,
                        $i->line, $i->name, $i->quantity, $i->unit, $i->rate, $i->amount,
                        $n === 0 ? $s->total : null,
                    ]);
                }
            }
        });

        return response()->download($xlsx->save('STO'), $name.'.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend();
    }

    /** Cadangan bila ekstensi PHP zip tidak aktif: CSV dengan desimal koma (format Excel Indonesia). */
    private function exportCsv(Builder $query, string $filename)
    {
        return response()->streamDownload(function () use ($query) {
            $f = fopen('php://output', 'w');
            fwrite($f, "\xEF\xBB\xBF");
            fputcsv($f, ['Nomor STO', 'Tanggal', 'Kegiatan', 'Asal', 'Tujuan', 'SOJ', 'MMJ', 'No', 'Nama Barang', 'Qty', 'Satuan', 'Tarif', 'Biaya', 'Total STO'], ';');
            $num = fn ($v, $d) => number_format((float) $v, $d, ',', '');
            $query->chunk(200, function ($stos) use ($f, $num) {
                foreach ($stos as $s) {
                    foreach ($s->items->values() as $n => $i) {
                        $row = [$s->number, $s->document_date->format('d/m/Y'), $s->activity, $this->oneLine($s->origin), $this->oneLine($s->destination), $s->soj, $s->mmj,
                            $i->line, $i->name, $num($i->quantity, 3), $i->unit, $num($i->rate, 2), $num($i->amount, 2), $n === 0 ? $num($s->total, 2) : ''];
                        // Cegah formula spreadsheet (CSV injection).
                        $row = array_map(fn ($v) => preg_match('/^[\s]*[=+@\-]/u', (string) $v) ? "'".$v : $v, $row);
                        fputcsv($f, $row, ';');
                    }
                }
            });
            fclose($f);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function oneLine(?string $value): string
    {
        return trim(preg_replace('/\s*\n\s*/', ', ', (string) $value));
    }
}
