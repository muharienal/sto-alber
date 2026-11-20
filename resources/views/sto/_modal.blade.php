{{--
    Modal detail satu STO: tampilan detail, isian SOJ/MMJ, ubah penuh (edit), dan hapus.
    Parameter: $sto (dengan relasi items dan batch)
--}}
@php
    $id = 'sto-'.$sto->id;
    $submitted = old('_modal') === $id;
    $formName = $submitted ? old('_form') : null;
    $editing = $formName === 'edit';
    $refSubmitted = $formName === 'references';
    $refBag = $refSubmitted ? $errors->getBag('sto') : new \Illuminate\Support\MessageBag();
    $autoOpen = $submitted || session('reopen') === $id || (string) request('open') === (string) $sto->id;
    $missing = $sto->missingReferences();
    $rupiah = fn ($v) => number_format((float) $v, 0, ',', '.');
    $qty = fn ($v) => \App\Support\Format::qty($v);
    $hasPdf = $sto->batch && $sto->source_page;
    $who = fn ($user) => $user?->name ?? '—';
    $fmtChange = function (string $field, $value) use ($rupiah) {
        if ($value === null || $value === '') { return 'kosong'; }
        return match ($field) {
            'document_date' => \Illuminate\Support\Carbon::parse($value)->format('d/m/Y'),
            'total' => 'Rp '.$rupiah($value),
            default => \Illuminate\Support\Str::limit(preg_replace('/\s+/', ' ', (string) $value), 60),
        };
    };
@endphp
<dialog class="modal modal-lg {{ $editing ? 'is-editing' : '' }}" id="{{ $id }}" aria-labelledby="{{ $id }}-title" @if($autoOpen) data-autoopen @endif>
    <div class="modal-head">
        <span class="chip-icon {{ $sto->activity === 'BONGKAR' ? 'tone-blue' : 'tone-teal' }}"><x-icon name="file" :size="20" /></span>
        <div class="titles">
            <h2 id="{{ $id }}-title">STO {{ $sto->number }} <span class="badge {{ $sto->activity === 'BONGKAR' ? 'badge-blue' : 'badge-teal' }}">{{ $sto->activity }}</span></h2>
            <p>
                <span data-view>@if($hasPdf){{ $sto->batch->original_name }} · halaman {{ $sto->source_page }}@else Input manual @endif</span>
                <span data-edit>Ubah data STO</span>
            </p>
        </div>
        <button type="button" class="icon-btn" data-dialog-close aria-label="Tutup"><x-icon name="x" :size="20" /></button>
    </div>

    <div class="modal-body">
        {{-- ===== Tampilan detail ===== --}}
        <div data-view class="view-stack">

            <section class="panel" aria-labelledby="{{ $id }}-refs-title">
                <h3 id="{{ $id }}-refs-title">SOJ &amp; MMJ @if($missing)<span class="badge badge-amber">{{ implode(' & ', $missing) }} kosong</span>@endif</h3>
                <form method="post" action="{{ route('sto.references', $sto) }}" data-loading>
                    @csrf @method('PATCH')
                    <input type="hidden" name="_modal" value="{{ $id }}">
                    <input type="hidden" name="_form" value="references">
                    @if($refBag->any())
                        <div class="alert alert-error" role="alert">
                            <x-icon name="alert" :size="18" />
                            <ul>@foreach($refBag->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
                        </div>
                    @endif
                    <div class="form-grid">
                        <div class="field">
                            <label class="field-label" for="{{ $id }}-soj">SOJ</label>
                            <input class="input {{ $refBag->has('soj') ? 'is-invalid' : '' }}" id="{{ $id }}-soj" name="soj" value="{{ $refSubmitted ? old('soj', '') : $sto->soj }}" maxlength="100" autocomplete="off">
                        </div>
                        <div class="field">
                            <label class="field-label" for="{{ $id }}-mmj">MMJ</label>
                            <input class="input {{ $refBag->has('mmj') ? 'is-invalid' : '' }}" id="{{ $id }}-mmj" name="mmj" value="{{ $refSubmitted ? old('mmj', '') : $sto->mmj }}" maxlength="100" autocomplete="off">
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-sm" data-submit data-loading-text="Menyimpan…"><x-icon name="check" :size="16" /><span>Simpan</span></button>
                    </div>
                </form>
            </section>

            <div class="detail-cols">
                <section class="panel">
                    <h3>Informasi</h3>
                    <dl class="dl">
                        <div><dt>Tanggal</dt><dd class="num">{{ $sto->document_date->format('d/m/Y') }}</dd></div>
                        <div><dt>Dibuat</dt><dd><span class="num">{{ $sto->created_at->format('d/m/Y H:i') }}</span>@if($sto->creator) · {{ $who($sto->creator) }}@endif</dd></div>
                        <div><dt>Terakhir diubah</dt><dd><span class="num">{{ $sto->updated_at->format('d/m/Y H:i') }}</span>@if($sto->updater ?? $sto->creator) · {{ $who($sto->updater ?? $sto->creator) }}@endif</dd></div>
                    </dl>
                </section>
                <section class="panel">
                    <h3>Rute</h3>
                    <div class="route-line">
                        <div><small>Asal</small><p>{{ $sto->origin }}</p></div>
                        <div><small>Tujuan</small><p>{{ $sto->destination }}</p></div>
                    </div>
                </section>
            </div>

            <section aria-labelledby="{{ $id }}-items-title">
                <h3 class="section-title" id="{{ $id }}-items-title">Barang <span class="count-pill">{{ $sto->items->count() }} baris</span></h3>
                <div class="table-card">
                    <div class="table-scroll" tabindex="0" role="region" aria-label="Barang STO {{ $sto->number }}">
                        <table class="data-table compact stack-table">
                            <thead>
                                <tr>
                                    <th scope="col">No</th>
                                    <th scope="col">Nama Barang</th>
                                    <th scope="col" class="ta-r">Qty</th>
                                    <th scope="col">Satuan</th>
                                    <th scope="col" class="ta-r">Tarif</th>
                                    <th scope="col" class="ta-r">Biaya</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sto->items as $item)
                                    <tr>
                                        <td data-label="No" class="col-no num">{{ $item->line }}</td>
                                        <td data-label="Nama Barang" class="name">{{ $item->name }}</td>
                                        <td data-label="Qty" class="num ta-r">{{ $qty($item->quantity) }}</td>
                                        <td data-label="Satuan">{{ $item->unit }}</td>
                                        <td data-label="Tarif" class="num ta-r">{{ $rupiah($item->rate) }}</td>
                                        <td data-label="Biaya" class="num ta-r">{{ $rupiah($item->amount) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th scope="row" colspan="5">Total</th>
                                    <td class="total ta-r" data-label="Total">Rp {{ $rupiah($sto->total) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </section>

            <section class="panel" aria-labelledby="{{ $id }}-log-title">
                <h3 id="{{ $id }}-log-title">Riwayat</h3>
                @if($sto->activities->isEmpty())
                    <p class="hint">Belum ada catatan. Riwayat mulai dicatat sejak fitur ini dipasang.</p>
                @else
                    <ol class="timeline">
                        @foreach($sto->activities->take(15) as $log)
                            <li class="tl-{{ $log->action }}">
                                <div class="tl-head">
                                    <strong>{{ $log->label() }}</strong>
                                    <span class="tl-meta">{{ $log->user_name ?? 'Sistem' }} · <span class="num">{{ $log->created_at->format('d/m/Y H:i') }}</span></span>
                                </div>
                                @if($log->note)<p class="tl-note">{{ $log->note }}</p>@endif
                                @if($log->changes)
                                    <ul class="tl-changes">
                                        @foreach($log->changes as $field => [$old, $new])
                                            <li><b>{{ \App\Models\StoActivity::FIELDS[$field] ?? $field }}</b>: <span class="old">{{ $field === 'items' ? $old : $fmtChange($field, $old) }}</span> → <span class="new">{{ $field === 'items' ? $new : $fmtChange($field, $new) }}</span></li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                @endif
            </section>
        </div>

        {{-- ===== Form ubah ===== --}}
        <div data-edit>
            @include('sto._form', [
                'formId' => 'form-'.$id,
                'action' => route('sto.update', $sto),
                'method' => 'PUT',
                'sto' => $sto,
                'modalId' => $id,
                'formName' => 'edit',
                'submitted' => $editing,
            ])
        </div>
    </div>

    {{-- ===== Footer: tampilan detail ===== --}}
    <div class="modal-foot" data-view>
        <div class="foot-main">
            @can('admin')
                <button type="button" class="btn btn-danger-soft" data-delete-toggle><x-icon name="trash" :size="18" />Hapus</button>
            @else
                <span></span>
            @endcan
            <div class="foot-actions">
                @if($hasPdf)
                    <a class="btn btn-secondary" target="_blank" rel="noopener" href="{{ route('sto.pdf', $sto) }}#page={{ $sto->source_page }}"><x-icon name="external" :size="18" />Buka PDF</a>
                @endif
                <button type="button" class="btn btn-primary" data-edit-toggle><x-icon name="edit" :size="18" />Ubah</button>
            </div>
        </div>
        @can('admin')
        <div class="foot-confirm">
            <p><strong>Hapus STO {{ $sto->number }}?</strong> STO dipindah ke Sampah dan masih bisa dipulihkan.</p>
            <div class="foot-actions">
                <button type="button" class="btn btn-secondary" data-delete-toggle>Batal</button>
                <form method="post" action="{{ route('sto.destroy', $sto) }}" data-loading>
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger" data-submit data-loading-text="Menghapus…"><x-icon name="trash" :size="18" /><span>Ya, hapus</span></button>
                </form>
            </div>
        </div>
        @endcan
    </div>

    {{-- ===== Footer: ubah ===== --}}
    <div class="modal-foot" data-edit>
        <p class="note">Biaya = Qty × Tarif, dihitung otomatis.</p>
        <div class="foot-actions">
            <button type="button" class="btn btn-secondary" data-edit-cancel>Batal</button>
            <button type="submit" form="form-{{ $id }}" class="btn btn-primary" data-submit data-loading-text="Menyimpan…"><x-icon name="check" :size="18" /><span>Simpan</span></button>
        </div>
    </div>
</dialog>
