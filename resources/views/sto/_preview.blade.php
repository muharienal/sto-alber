{{-- ============ Modal pratinjau impor ============ --}}
@php
    $newCount = collect($rows)->where('duplicate', false)->count();
    $dupCount = collect($rows)->where('duplicate', true)->count();
    $failed = $batch->failed ?? [];
    $failCount = count($failed);
@endphp
<dialog class="modal" id="preview-modal" open autofocus data-autoopen data-persistent aria-labelledby="preview-title">
    <div class="modal-head">
        <span class="chip-icon tone-blue"><x-icon name="file" :size="20" /></span>
        <div class="titles">
            <h2 id="preview-title">Pratinjau Impor</h2>
            <p title="{{ $batch->original_name }}">{{ $batch->original_name }} · belum tersimpan</p>
        </div>
        <button type="button" class="icon-btn" data-confirm-toggle aria-label="Tutup dan batalkan impor"><x-icon name="x" :size="20" /></button>
    </div>

    <div class="modal-body">
        <div class="summary">
            <div><span class="chip-icon tone-dark"><x-icon name="layers" :size="20" /></span><div><strong>{{ count($rows) }}</strong><span>STO terbaca</span></div></div>
            <div><span class="chip-icon tone-green"><x-icon name="check-circle" :size="20" /></span><div><strong>{{ $newCount }}</strong><span>Akan disimpan</span></div></div>
            <div><span class="chip-icon {{ $dupCount ? 'tone-amber' : 'tone-gray' }}"><x-icon :name="$dupCount ? 'alert' : 'shield'" :size="20" /></span><div><strong>{{ $dupCount }}</strong><span>Duplikat (dilewati)</span></div></div>
            @if($failCount)
                <div><span class="chip-icon tone-red"><x-icon name="file-alert" :size="20" /></span><div><strong>{{ $failCount }}</strong><span>Gagal dibaca</span></div></div>
            @endif
        </div>



        @if($failCount)
            <section class="failed-box" aria-labelledby="failed-title">
                <div class="failed-head">
                    <x-icon name="file-alert" :size="18" />
                    <div>
                        <strong id="failed-title">{{ $failCount }} STO gagal dibaca</strong>
                        <p>Tidak ikut tersimpan otomatis. Setelah klik Simpan, STO ini muncul di Master STO dengan tombol <b>Tambah manual</b> yang sudah terisi dari bagian yang terbaca.</p>
                    </div>
                </div>
                <ul class="failed-list">
                    @foreach($failed as $f)
                        <li>
                            <span class="sto-no">{{ $f['number'] }}</span>
                            <span class="fl-msg">{{ $f['message'] }}</span>
                            <a class="btn btn-ghost btn-sm" href="{{ route('sto.batch-pdf', $batch) }}#page={{ $f['source_page'] }}" target="_blank" rel="noopener"><x-icon name="external" :size="15" />Halaman {{ $f['source_page'] }}</a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if(count($rows))
        <div class="table-card">
            <div class="table-scroll" tabindex="0" role="region" aria-label="Hasil bacaan PDF">
                <table class="data-table compact stack-table">
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Nomor STO</th>
                            <th scope="col">Halaman</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Kegiatan</th>
                            <th scope="col">Rute</th>
                            <th scope="col">Barang</th>
                            <th scope="col" class="ta-r">Total</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            <tr class="{{ $row['duplicate'] ? 'is-dup' : '' }}">
                                <td data-label="No" class="col-no num">{{ $loop->iteration }}</td>
                                <td data-label="Nomor STO"><span class="sto-no">{{ $row['number'] }}</span></td>
                                <td data-label="Halaman" class="num">{{ $row['source_page'] }}</td>
                                <td data-label="Tanggal" class="num" style="white-space:nowrap">{{ $date($row['document_date']) }}</td>
                                <td data-label="Kegiatan"><span class="badge {{ $row['activity'] === 'BONGKAR' ? 'badge-blue' : 'badge-teal' }}">{{ $row['activity'] }}</span></td>
                                <td data-label="Rute" class="route-cell">
                                    <div>
                                        <div><small>Asal</small><span class="addr">{{ $row['origin'] }}</span></div>
                                        <div class="to"><small>Tujuan</small><span class="addr">{{ $row['destination'] }}</span></div>
                                    </div>
                                </td>
                                <td data-label="Barang">
                                    <div class="line-items">
                                        @foreach($row['items'] as $item)
                                            <div class="li">
                                                <strong>{{ $item['name'] }}</strong>
                                                <span>{{ $qty($item['quantity']) }} {{ $item['unit'] }} × {{ $rupiah($item['rate']) }} = {{ $rupiah($item['amount']) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td data-label="Total" class="total ta-r">{{ $rupiah($row['total']) }}</td>
                                <td data-label="Status">
                                    <div>
                                        <span class="badge {{ $row['duplicate'] ? 'badge-amber' : 'badge-green' }}">{{ $row['trashed'] ?? false ? 'Ada di Sampah' : ($row['duplicate'] ? 'Duplikat' : 'Baru') }}</span>
                                        @foreach($row['notes'] as $note)<p class="notes">{{ $note }}</p>@endforeach
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    <div class="modal-foot">
        <div class="foot-main">
            <p class="note">Berlaku sampai {{ $batch->expires_at->format('d/m/Y H:i') }}.</p>
            <div class="foot-actions">
                <button type="button" class="btn btn-secondary" data-confirm-toggle>Batalkan</button>
                <form id="commit-form" method="post" action="{{ route('sto.commit', $batch) }}" data-loading>
                    @csrf
                    <button type="submit" class="btn btn-primary" data-submit data-loading-text="Menyimpan…" @disabled($newCount === 0 && $failCount === 0)>
                        <x-icon name="check" :size="18" /><span>{{ $newCount > 0 ? 'Simpan '.$newCount.' STO' : ($failCount ? 'Lanjut ke tambah manual' : 'Tidak ada STO baru') }}</span>
                    </button>
                </form>
            </div>
        </div>
        <div class="foot-confirm">
            <p><strong>Batalkan impor?</strong> Hasil bacaan dibuang dan PDF yang diunggah dihapus. Tidak ada STO yang tersimpan.</p>
            <div class="foot-actions">
                <button type="button" class="btn btn-secondary" data-confirm-toggle>Kembali</button>
                <form id="cancel-form" method="post" action="{{ route('sto.cancel', $batch) }}" data-loading>
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger" data-submit data-loading-text="Membatalkan…"><span>Ya, batalkan</span></button>
                </form>
            </div>
        </div>
    </div>
</dialog>
