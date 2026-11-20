@extends('layouts.app')
@section('title', 'Sampah')

@section('content')
@php $rupiah = fn ($v) => number_format((float) $v, 0, ',', '.'); @endphp

<div class="page-head">
    <div>
        <h1>Sampah</h1>
        <p class="lead">STO yang dihapus. Pulihkan bila terhapus tidak sengaja, atau hapus permanen.</p>
    </div>
</div>

<section class="card list-card" aria-labelledby="trash-title">
    <div class="card-head">
        <h2 id="trash-title">STO di Sampah <span class="count-pill">{{ number_format($stos->total(), 0, ',', '.') }} STO</span></h2>
    </div>

    @if($stos->isEmpty())
        <div class="list-empty">
            <div class="empty">
                <span class="chip-icon"><x-icon name="trash" :size="26" /></span>
                <h3>Sampah kosong</h3>
                <p>STO yang dihapus akan muncul di sini dan masih bisa dipulihkan.</p>
            </div>
        </div>
    @else
        <div class="table-scroll" tabindex="0" role="region" aria-label="STO di Sampah">
            <table class="data-table stack-table">
                <thead>
                    <tr>
                        <th scope="col">Nomor STO</th>
                        <th scope="col">Tanggal</th>
                        <th scope="col">Kegiatan</th>
                        <th scope="col">Barang</th>
                        <th scope="col" class="ta-r">Total</th>
                        <th scope="col">Dihapus</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stos as $sto)
                        <tr data-confirm-scope>
                            <td data-label="Nomor STO"><span class="sto-no">{{ $sto->number }}</span></td>
                            <td data-label="Tanggal" class="num">{{ $sto->document_date->format('d/m/Y') }}</td>
                            <td data-label="Kegiatan"><span class="badge {{ $sto->activity === 'BONGKAR' ? 'badge-blue' : 'badge-teal' }}">{{ $sto->activity }}</span></td>
                            <td data-label="Barang">{{ $sto->items->pluck('name')->implode(', ') ?: '—' }}</td>
                            <td data-label="Total" class="total ta-r">{{ $rupiah($sto->total) }}</td>
                            <td data-label="Dihapus"><span class="num">{{ $sto->deleted_at->format('d/m/Y H:i') }}</span><br><span class="hint">{{ $sto->deleter?->name ?? '—' }}</span></td>
                            <td data-label="Aksi">
                                <div class="trash-actions">
                                    <form method="post" action="{{ route('sto.restore', $sto->id) }}" data-loading>
                                        @csrf
                                        <button type="submit" class="btn btn-secondary btn-sm" data-submit data-loading-text="Memulihkan…"><x-icon name="rotate" :size="15" /><span>Pulihkan</span></button>
                                    </form>
                                    <button type="button" class="btn btn-ghost btn-sm" data-confirm-toggle>Hapus permanen</button>
                                </div>
                                <div class="trash-confirm">
                                    <p>Hapus permanen? Tidak bisa dipulihkan lagi.</p>
                                    <div class="trash-actions">
                                        <button type="button" class="btn btn-secondary btn-sm" data-confirm-toggle>Batal</button>
                                        <form method="post" action="{{ route('sto.force-destroy', $sto->id) }}" data-loading>
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" data-submit data-loading-text="Menghapus…"><span>Ya, hapus</span></button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $stos->links() }}
    @endif
</section>
@endsection
