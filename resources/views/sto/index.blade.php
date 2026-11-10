@extends('layouts.app')
@section('title', 'Master STO')
@section('local_errors', '1')

@section('content')
@php
    $hasFilter = request()->filled('q') || request()->filled('activity') || request()->filled('completeness');
    $rupiah = fn ($v) => number_format((float) $v, 0, ',', '.');
    $qty = fn ($v) => number_format((float) $v, 3, ',', '.');
    $date = fn ($v) => \Illuminate\Support\Carbon::parse($v)->format('d/m/Y');
    $createSubmitted = old('_modal') === 'create-sto';
@endphp

<div class="page-head">
    <div>
        <h1>Master STO</h1>
        <p class="lead">Impor STO dari PDF atau tambah manual, lalu lengkapi SOJ dan MMJ.</p>
    </div>
</div>

{{-- ============ Unggah PDF ============ --}}
<section class="card" aria-labelledby="upload-title">
    <h2 class="sr-only" id="upload-title">Unggah PDF STO</h2>

    @if($errors->any())
        <div class="alert alert-danger" role="alert" style="margin-bottom:20px">
            <x-icon name="alert" :size="20" />
            <div>
                <strong>PDF gagal diproses</strong>
                <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        </div>
    @endif

    <form method="post" action="{{ route('sto.upload') }}" enctype="multipart/form-data" data-loading data-upload-form>
        @csrf
        <div class="upload-grid">
            <label class="dropzone" data-dropzone for="pdf">
                <input class="sr-only" type="file" id="pdf" name="pdf" accept="application/pdf,.pdf" required aria-label="Pilih PDF STO">
                <span class="dz-icon"><x-icon name="upload" :size="28" /></span>
                <span class="dz-title" data-dz-title>Pilih PDF STO</span>
                <span class="dz-hint" data-dz-hint>PDF teks (bukan scan), maks. 10 MB / 200 halaman</span>
                <span class="btn btn-secondary btn-sm dz-btn">Pilih file atau seret ke sini</span>
            </label>

            <div class="upload-side">
                <ol class="steps">
                    <li><div><strong>Unggah PDF</strong><br><span class="hint">Satu file boleh berisi banyak STO.</span></div></li>
                    <li><div><strong>Periksa pratinjau</strong><br><span class="hint">Data belum tersimpan pada tahap ini.</span></div></li>
                    <li><div><strong>Simpan</strong><br><span class="hint">Nomor STO yang sudah ada dilewati.</span></div></li>
                </ol>

                <button type="submit" class="btn btn-primary btn-lg btn-block" data-submit data-loading-text="Membaca PDF…">
                    <x-icon name="eye" :size="18" /><span>Pratinjau</span>
                </button>
            </div>
        </div>
    </form>
</section>

{{-- ============ Daftar STO ============ --}}
<div class="section-head">
    <h2>Daftar STO <span class="count-pill">{{ number_format($stos->total(), 0, ',', '.') }} STO</span></h2>
    <div class="head-actions">
        <a class="btn btn-secondary" href="{{ route('sto.export') }}"><x-icon name="download" :size="18" />Unduh CSV</a>
        <button type="button" class="btn btn-primary" data-dialog-open="create-sto"><x-icon name="plus" :size="18" />Tambah STO</button>
    </div>
</div>

@if($incompleteCount > 0 && request('completeness') !== 'incomplete')
    <div class="alert alert-warn" role="status">
        <x-icon name="alert" :size="20" />
        <p><strong>{{ number_format($incompleteCount, 0, ',', '.') }} STO belum lengkap.</strong> SOJ atau MMJ masih kosong. <a href="{{ route('sto.index', ['completeness' => 'incomplete']) }}">Lihat daftar</a></p>
    </div>
@endif

<form class="filters" method="get" action="{{ route('sto.index') }}" role="search">
    <div class="input-icon">
        <x-icon name="search" :size="18" />
        <input class="input" type="search" name="q" value="{{ request('q') }}" placeholder="Cari nomor, asal, tujuan, SOJ, MMJ" aria-label="Cari nomor, asal, tujuan, SOJ, atau MMJ" maxlength="100">
    </div>
    <select class="select" name="activity" aria-label="Filter kegiatan">
        <option value="">Semua kegiatan</option>
        @foreach(['BONGKAR', 'MUAT'] as $v)
            <option value="{{ $v }}" @selected(request('activity') === $v)>{{ $v }}</option>
        @endforeach
    </select>
    <select class="select" name="completeness" aria-label="Filter kelengkapan SOJ dan MMJ">
        <option value="">Semua kelengkapan</option>
        <option value="incomplete" @selected(request('completeness') === 'incomplete')>SOJ/MMJ belum lengkap</option>
        <option value="complete" @selected(request('completeness') === 'complete')>SOJ/MMJ lengkap</option>
    </select>
    <button type="submit" class="btn btn-navy">Cari</button>
    @if($hasFilter)
        <a class="btn btn-ghost" href="{{ route('sto.index') }}"><x-icon name="rotate" :size="16" />Reset</a>
    @endif
</form>

@if($stos->isEmpty())
    <div class="table-card">
        <div class="empty">
            <span class="chip-icon"><x-icon name="inbox" :size="26" /></span>
            @if($hasFilter)
                <h3>Tidak ada STO yang cocok</h3>
                <p>Ubah kata kunci atau reset filter.</p>
                <a class="btn btn-secondary btn-sm" href="{{ route('sto.index') }}" style="margin-top:10px">Reset filter</a>
            @else
                <h3>Belum ada STO</h3>
                <p>Unggah PDF di atas atau tambah STO manual.</p>
                <button type="button" class="btn btn-primary btn-sm" data-dialog-open="create-sto" style="margin-top:10px"><x-icon name="plus" :size="16" />Tambah STO</button>
            @endif
        </div>
    </div>
@else
    {{-- Tabel (desktop) --}}
    <div class="table-card desk-only">
        <div class="table-scroll" tabindex="0" role="region" aria-label="Tabel STO">
            <table class="data-table">
                <thead>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Nomor STO</th>
                        <th scope="col">Tanggal</th>
                        <th scope="col">Kegiatan</th>
                        <th scope="col">Asal</th>
                        <th scope="col">Tujuan</th>
                        <th scope="col">Nama Barang</th>
                        <th scope="col" class="ta-r">Qty</th>
                        <th scope="col">Satuan</th>
                        <th scope="col" class="ta-r">Tarif</th>
                        <th scope="col" class="ta-r">Total</th>
                        <th scope="col">SOJ / MMJ</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                @foreach($stos as $sto)
                    @php
                        $items = $sto->items->values();
                        $span = max(1, $items->count());
                        $no = $stos->firstItem() + $loop->index;
                    @endphp
                    <tbody class="group">
                        @for($i = 0; $i < $span; $i++)
                            @php $item = $items[$i] ?? null; @endphp
                            <tr>
                                @if($i === 0)
                                    <td rowspan="{{ $span }}" class="col-no num">{{ $no }}</td>
                                    <td rowspan="{{ $span }}"><button type="button" class="sto-no link-btn" data-dialog-open="sto-{{ $sto->id }}">{{ $sto->number }}</button></td>
                                    <td rowspan="{{ $span }}" class="num" style="white-space:nowrap">{{ $sto->document_date->format('d/m/Y') }}</td>
                                    <td rowspan="{{ $span }}"><span class="badge {{ $sto->activity === 'BONGKAR' ? 'badge-blue' : 'badge-teal' }}">{{ $sto->activity }}</span></td>
                                    <td rowspan="{{ $span }}" class="place"><div class="clamp" title="{{ $sto->origin }}">{{ $sto->origin }}</div></td>
                                    <td rowspan="{{ $span }}" class="place"><div class="clamp" title="{{ $sto->destination }}">{{ $sto->destination }}</div></td>
                                @endif
                                <td class="item name">{{ $item?->name ?? '—' }}</td>
                                <td class="item ta-r">{{ $item ? $qty($item->quantity) : '—' }}</td>
                                <td class="item">{{ $item?->unit ?? '—' }}</td>
                                <td class="item ta-r">{{ $item ? $rupiah($item->rate) : '—' }}</td>
                                @if($i === 0)
                                    <td rowspan="{{ $span }}" class="total ta-r">{{ $rupiah($sto->total) }}</td>
                                    <td rowspan="{{ $span }}" class="refs">
                                        <div class="ref"><small>SOJ</small>@if(filled($sto->soj))<span>{{ $sto->soj }}</span>@else<span class="badge badge-amber">Kosong</span>@endif</div>
                                        <div class="ref"><small>MMJ</small>@if(filled($sto->mmj))<span>{{ $sto->mmj }}</span>@else<span class="badge badge-amber">Kosong</span>@endif</div>
                                    </td>
                                    <td rowspan="{{ $span }}">
                                        <div class="row-actions">
                                            <button type="button" class="btn btn-secondary btn-sm" data-dialog-open="sto-{{ $sto->id }}">Detail</button>
                                            @if($sto->batch && $sto->source_page)
                                                <a class="btn btn-secondary btn-sm" target="_blank" rel="noopener" href="{{ route('sto.pdf', $sto) }}#page={{ $sto->source_page }}"><x-icon name="external" :size="15" />PDF</a>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endfor
                    </tbody>
                @endforeach
            </table>
        </div>
    </div>

    {{-- Kartu (HP & tablet kecil) --}}
    <div class="sto-cards" aria-label="Daftar STO">
        @foreach($stos as $sto)
            <article class="sto-card">
                <div class="top">
                    <button type="button" class="sto-no link-btn" data-dialog-open="sto-{{ $sto->id }}">{{ $sto->number }}</button>
                    <span class="date num">{{ $sto->document_date->format('d/m/Y') }}</span>
                </div>
                <div class="badges">
                    <span class="badge {{ $sto->activity === 'BONGKAR' ? 'badge-blue' : 'badge-teal' }}">{{ $sto->activity }}</span>
                    @if($sto->missingReferences())
                        <span class="badge badge-amber">{{ implode(' & ', $sto->missingReferences()) }} kosong</span>
                    @endif
                </div>
                <div class="route">
                    <div><small>Asal</small><p>{{ $sto->origin }}</p></div>
                    <div><small>Tujuan</small><p>{{ $sto->destination }}</p></div>
                </div>
                <div class="route">
                    <div><small>SOJ</small><p>{{ filled($sto->soj) ? $sto->soj : '—' }}</p></div>
                    <div><small>MMJ</small><p>{{ filled($sto->mmj) ? $sto->mmj : '—' }}</p></div>
                </div>
                <ul class="items">
                    @foreach($sto->items as $item)
                        <li>
                            <b>{{ $item->name }}</b>
                            <span>{{ $qty($item->quantity) }} {{ $item->unit }}<br>× {{ $rupiah($item->rate) }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="sum"><span>Total</span><strong>Rp {{ $rupiah($sto->total) }}</strong></div>
                <div class="actions">
                    <button type="button" class="btn btn-secondary btn-sm" data-dialog-open="sto-{{ $sto->id }}">Detail</button>
                    @if($sto->batch && $sto->source_page)
                        <a class="btn btn-secondary btn-sm" target="_blank" rel="noopener" href="{{ route('sto.pdf', $sto) }}#page={{ $sto->source_page }}"><x-icon name="external" :size="15" />PDF</a>
                    @endif
                </div>
            </article>
        @endforeach
    </div>

    {{ $stos->links() }}
@endif

{{-- ============ Modal tambah STO ============ --}}
<dialog class="modal modal-lg is-editing" id="create-sto" data-always-edit aria-labelledby="create-sto-title" @if($createSubmitted) data-autoopen @endif>
    <div class="modal-head">
        <span class="chip-icon tone-orange"><x-icon name="plus" :size="20" /></span>
        <div class="titles">
            <h2 id="create-sto-title">Tambah STO</h2>
            <p>SOJ dan MMJ boleh diisi nanti.</p>
        </div>
        <button type="button" class="icon-btn" data-dialog-close aria-label="Tutup"><x-icon name="x" :size="20" /></button>
    </div>
    <div class="modal-body">
        @include('sto._form', [
            'formId' => 'form-create-sto',
            'action' => route('sto.store'),
            'method' => 'POST',
            'sto' => null,
            'modalId' => 'create-sto',
            'formName' => 'create',
            'submitted' => $createSubmitted,
        ])
    </div>
    <div class="modal-foot">
        <p class="note">Biaya = Qty × Tarif, dihitung otomatis.</p>
        <div class="foot-actions">
            <button type="button" class="btn btn-secondary" data-dialog-close>Batal</button>
            <button type="submit" form="form-create-sto" class="btn btn-primary" data-submit data-loading-text="Menyimpan…"><x-icon name="check" :size="18" /><span>Simpan</span></button>
        </div>
    </div>
</dialog>

{{-- ============ Modal detail tiap STO ============ --}}
@foreach($dialogStos as $dialogSto)
    @include('sto._modal', ['sto' => $dialogSto])
@endforeach

{{-- ============ Modal pratinjau impor ============ --}}
@isset($batch)
@php
    $newCount = collect($rows)->where('duplicate', false)->count();
    $dupCount = collect($rows)->where('duplicate', true)->count();
@endphp
<dialog class="modal" id="preview-modal" open autofocus data-autoopen data-persistent aria-labelledby="preview-title">
    <div class="modal-head">
        <span class="chip-icon"><x-icon name="file" :size="20" /></span>
        <div class="titles">
            <h2 id="preview-title">Pratinjau Impor</h2>
            <p title="{{ $batch->original_name }}">{{ $batch->original_name }}</p>
        </div>
        <button type="submit" form="cancel-form" class="icon-btn" aria-label="Tutup dan batalkan impor"><x-icon name="x" :size="20" /></button>
    </div>

    <div class="modal-body">
        <div class="summary">
            <div><span class="chip-icon tone-blue"><x-icon name="layers" :size="20" /></span><div><strong>{{ count($rows) }}</strong><span>STO terbaca</span></div></div>
            <div><span class="chip-icon tone-green"><x-icon name="check-circle" :size="20" /></span><div><strong>{{ $newCount }}</strong><span>Akan disimpan</span></div></div>
            <div><span class="chip-icon {{ $dupCount ? 'tone-amber' : 'tone-teal' }}"><x-icon :name="$dupCount ? 'alert' : 'shield'" :size="20" /></span><div><strong>{{ $dupCount }}</strong><span>Duplikat (dilewati)</span></div></div>
        </div>

        <div class="alert alert-info">
            <x-icon name="info" :size="20" />
            <p>Data belum tersimpan. Periksa hasil bacaan, lalu klik <strong>Simpan</strong>.</p>
        </div>

        @if(count($batch->warnings))
            <div class="alert alert-danger" role="alert">
                <x-icon name="alert" :size="20" />
                <div>
                    <strong>Gagal dibaca (tidak disimpan):</strong>
                    <ul>@foreach($batch->warnings as $w)<li>{{ $w }}</li>@endforeach</ul>
                </div>
            </div>
        @endif

        <div class="table-card">
            <div class="table-scroll" tabindex="0" role="region" aria-label="Hasil bacaan PDF">
                <table class="data-table compact stack-table">
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Nomor STO</th>
                            <th scope="col">Hal.</th>
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
                                        <span class="badge {{ $row['duplicate'] ? 'badge-amber' : 'badge-green' }}">{{ $row['duplicate'] ? 'Duplikat' : 'Baru' }}</span>
                                        @foreach($row['notes'] as $note)<p class="notes">{{ $note }}</p>@endforeach
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal-foot">
        <p class="note">Berlaku sampai {{ $batch->expires_at->format('d/m/Y H:i') }}. Menutup jendela ini membatalkan impor.</p>
        <div class="foot-actions">
            <form id="cancel-form" method="post" action="{{ route('sto.cancel', $batch) }}" data-loading>
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-secondary" data-submit data-loading-text="Membatalkan…">Batalkan</button>
            </form>
            <form id="commit-form" method="post" action="{{ route('sto.commit', $batch) }}" data-loading>
                @csrf
                <button type="submit" class="btn btn-primary" data-submit data-loading-text="Menyimpan…" @disabled($newCount === 0)>
                    <x-icon name="check" :size="18" /><span>{{ $newCount === 0 ? 'Tidak ada STO baru' : 'Simpan '.$newCount.' STO' }}</span>
                </button>
            </form>
        </div>
    </div>
</dialog>
@endisset
@endsection
