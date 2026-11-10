<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoRequest;
use App\Models\{ImportBatch, Sto, StoItem};
use App\Services\StoPdfParser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Cache, DB, Storage};
use Illuminate\Validation\ValidationException;

class StoController extends Controller
{
    /* ------------------------------------------------------------------
     |  Daftar (Read)
     * ------------------------------------------------------------------ */

    private function filtered(Request $r): Builder
    {
        $query = Sto::with(['items', 'batch']);

        if ($r->filled('q')) {
            $s = substr((string) $r->q, 0, 100);
            $query->where(function ($q) use ($s) {
                $q->where('number', 'like', "%$s%")
                    ->orWhere('origin', 'like', "%$s%")
                    ->orWhere('destination', 'like', "%$s%")
                    ->orWhere('soj', 'like', "%$s%")
                    ->orWhere('mmj', 'like', "%$s%");
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

        return $query;
    }

    /** Data untuk halaman Master STO (juga dipakai saat pratinjau impor tampil sebagai modal). */
    private function indexData(Request $r): array
    {
        $stos = $this->filtered($r)
            ->latest()->orderByDesc('id')
            ->paginate(20)
            ->withPath(route('sto.index'))
            ->appends($r->except('open'));

        // Setiap STO pada halaman ini punya modal detail. STO yang diminta lewat
        // ?open=ID atau dibuka ulang setelah simpan ditambahkan bila tidak ada di halaman ini.
        $dialogStos = $stos->getCollection()->values();
        $reopen = (string) session('reopen');
        $openId = $r->query('open') ?? (str_starts_with($reopen, 'sto-') ? substr($reopen, 4) : null);
        if ($openId !== null && ctype_digit((string) $openId) && ! $dialogStos->contains('id', (int) $openId)) {
            $extra = Sto::with(['items', 'batch'])->find((int) $openId);
            if ($extra) {
                $dialogStos->push($extra);
            }
        }

        return [
            'stos' => $stos,
            'dialogStos' => $dialogStos,
            'incompleteCount' => Sto::incomplete()->count(),
        ];
    }

    public function index(Request $r)
    {
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
        $r->validate(['pdf' => 'required|file|mimes:pdf|extensions:pdf|max:10240'], [
            'pdf.required' => 'Pilih file PDF terlebih dahulu.',
            'pdf.file' => 'File gagal diunggah. Coba lagi.',
            'pdf.uploaded' => 'File gagal diunggah. Pastikan ukurannya maks. 10 MB.',
            'pdf.mimes' => 'File harus berformat PDF.',
            'pdf.extensions' => 'File harus berformat PDF.',
            'pdf.max' => 'Ukuran PDF maks. 10 MB.',
        ]);
        $path = $r->file('pdf')->store('sto', 'local');
        try {
            $result = $parser->parse(Storage::path($path));
        } catch (\Throwable $e) {
            Storage::delete($path);
            report($e);
            throw ValidationException::withMessages(['pdf' => $e instanceof \RuntimeException ? $e->getMessage() : 'PDF tidak bisa dibuka. Pastikan file tidak rusak atau terkunci.']);
        }
        try {
            $batch = ImportBatch::create([
                'user_id' => $r->user()->id,
                'original_name' => $r->file('pdf')->getClientOriginalName(),
                'path' => $path,
                'sha256' => hash_file('sha256', Storage::path($path)),
                'payload' => $result['rows'],
                'warnings' => $result['warnings'],
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
        $existing = Sto::whereIn('number', array_column($batch->payload, 'number'))->pluck('number')->all();
        $rows = array_map(function ($row) use (&$seen, $existing) {
            $row['duplicate'] = isset($seen[$row['number']]) || in_array($row['number'], $existing, true);
            $seen[$row['number']] = true;

            return $row;
        }, $batch->payload);

        // Pratinjau tampil sebagai modal di atas halaman Master STO.
        return view('sto.index', $this->indexData($r) + ['batch' => $batch, 'rows' => $rows]);
    }

    public function commit(Request $r, ImportBatch $batch)
    {
        $this->own($r, $batch);

        return Cache::lock('sto-import-global', 120)->block(5, function () use ($batch) {
            $counts = DB::transaction(function () use ($batch) {
                $b = ImportBatch::whereKey($batch->id)->lockForUpdate()->firstOrFail();
                abort_unless($b->status === 'preview' && $b->expires_at->isFuture(), 410, 'Pratinjau tidak berlaku.');
                $saved = 0;
                $skip = 0;
                foreach ($b->payload as $row) {
                    if (Sto::where('number', $row['number'])->exists()) {
                        $skip++;
                        continue;
                    }
                    $items = $row['items'];
                    unset($row['items'], $row['notes']);
                    $sto = Sto::create($row + ['import_batch_id' => $b->id]);
                    $sto->items()->createMany($items);
                    $saved++;
                }
                $b->update(['status' => 'committed', 'payload' => []]);

                return [$saved, $skip];
            });

            $message = "{$counts[0]} STO disimpan.".($counts[1] ? " {$counts[1]} duplikat dilewati." : '');

            return redirect()->route('sto.index')->with('success', $message);
        });
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
        $sto = DB::transaction(fn () => $this->persist(new Sto(), $request->validated()));

        return redirect()->route('sto.index')->with([
            'success' => "STO {$sto->number} ditambahkan.",
            'reopen' => 'sto-'.$sto->id,
        ]);
    }

    public function update(StoRequest $request, Sto $sto)
    {
        $saved = DB::transaction(function () use ($request, $sto) {
            $locked = Sto::whereKey($sto->id)->lockForUpdate()->firstOrFail();

            return $this->persist($locked, $request->validated());
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
        ], StoRequest::indonesianMessages(), ['soj' => 'SOJ', 'mmj' => 'MMJ']);

        $sto->update([
            'soj' => $this->nullable($data['soj'] ?? null),
            'mmj' => $this->nullable($data['mmj'] ?? null),
        ]);

        return back()->with([
            'success' => "SOJ/MMJ STO {$sto->number} disimpan.",
            'reopen' => 'sto-'.$sto->id,
        ]);
    }

    public function destroy(Sto $sto)
    {
        $number = $sto->number;
        DB::transaction(function () use ($sto) {
            Sto::whereKey($sto->id)->lockForUpdate()->firstOrFail()->delete();
        });

        return redirect()->route('sto.index')->with('success', "STO {$number} dihapus.");
    }

    /** Menyimpan STO beserta rincian barangnya; biaya dan total dihitung di server. */
    private function persist(Sto $sto, array $data): Sto
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

    public function export()
    {
        return response()->streamDownload(function () {
            $f = fopen('php://output', 'w');
            fwrite($f, "\xEF\xBB\xBF");
            fputcsv($f, ['Nomor STO', 'Tanggal', 'Kegiatan', 'Asal', 'Tujuan', 'SOJ', 'MMJ', 'Nama Barang', 'Qty', 'Satuan', 'Tarif', 'Biaya', 'Total STO'], ';');
            Sto::with('items')->orderBy('id')->chunkById(100, function ($stos) use ($f) {
                foreach ($stos as $s) {
                    foreach ($s->items as $i) {
                        $row = [$s->number, $s->document_date->format('Y-m-d'), $s->activity, $s->origin, $s->destination, $s->soj, $s->mmj, $i->name, $i->quantity, $i->unit, $i->rate, $i->amount, $s->total];
                        // Cegah formula spreadsheet (CSV injection).
                        $row = array_map(fn ($v) => preg_match('/^[\s]*[=+@\-]/u', (string) $v) ? "'".$v : $v, $row);
                        fputcsv($f, $row, ';');
                    }
                }
            });
            fclose($f);
        }, 'laporan-sto-'.now()->format('Ymd').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
