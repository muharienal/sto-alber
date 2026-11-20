@extends('layouts.app')
@section('title', 'Rekap')

@section('content')
@php
    use App\Support\Format;
    $rp = fn ($v) => Format::rupiah($v);
    $ton = fn ($v) => Format::ton($v);
    $tables = [
        ['id' => 'rekap-bulan', 'title' => 'Per Bulan', 'head' => 'Bulan', 'rows' => $byMonth, 'icon' => 'calendar'],
        ['id' => 'rekap-kegiatan', 'title' => 'Per Kegiatan', 'head' => 'Kegiatan', 'rows' => $byActivity, 'icon' => 'layers'],
        ['id' => 'rekap-rute', 'title' => 'Per Rute', 'head' => 'Rute (asal → tujuan)', 'rows' => $byRoute, 'icon' => 'arrow-right'],
    ];
@endphp

<div class="page-head">
    <div>
        <h1>Rekap</h1>
        <p class="lead">{{ $period->label() }} · menurut tanggal STO</p>
    </div>
    <div class="head-actions no-print">
        <button type="button" class="btn btn-secondary" data-print><x-icon name="file" :size="16" />Cetak / Simpan PDF</button>
    </div>
</div>

<div class="no-print">@include('partials.period', ['periodRoute' => 'rekap'])</div>

<div class="rekap-sum">
    <div><span>STO</span><strong>{{ $rp($summary['stos']) }}</strong></div>
    <div><span>Tonase</span><strong>{{ $ton($summary['ton']) }} <small>TON</small></strong></div>
    <div><span>Nilai</span><strong><small>Rp</small> {{ $rp($summary['amount']) }}</strong></div>
</div>

@if($summary['stos'] === 0)
    <section class="card">
        <div class="empty">
            <span class="chip-icon"><x-icon name="chart" :size="26" /></span>
            <h3>Tidak ada STO pada periode ini</h3>
            <p>Pilih periode lain di atas.</p>
        </div>
    </section>
@else
    <div class="rekap-grid">
        @foreach($tables as $t)
            <section class="card rekap-card" aria-labelledby="{{ $t['id'] }}">
                <div class="card-head"><div class="card-title"><span class="chip-icon tone-yellow"><x-icon :name="$t['icon']" :size="18" /></span><h2 id="{{ $t['id'] }}">{{ $t['title'] }}</h2></div></div>
                <div class="table-scroll" tabindex="0" role="region" aria-label="Rekap {{ strtolower($t['title']) }}">
                    <table class="data-table rekap-table">
                        <thead><tr><th scope="col">{{ $t['head'] }}</th><th scope="col" class="ta-r">STO</th><th scope="col" class="ta-r">Tonase</th><th scope="col" class="ta-r">Nilai (Rp)</th></tr></thead>
                        <tbody>
                            @foreach($t['rows'] as $row)
                                <tr>
                                    <th scope="row">{{ $row['label'] }}</th>
                                    <td class="num ta-r">{{ $rp($row['stos']) }}</td>
                                    <td class="num ta-r">{{ $ton($row['ton']) }}</td>
                                    <td class="num ta-r">{{ $rp($row['amount']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        @if(count($t['rows']) > 1)
                            <tfoot><tr><th scope="row">Total</th><td class="num ta-r">{{ $rp($summary['stos']) }}</td><td class="num ta-r">{{ $ton($summary['ton']) }}</td><td class="num ta-r">{{ $rp($summary['amount']) }}</td></tr></tfoot>
                        @endif
                    </table>
                </div>
            </section>
        @endforeach

        <section class="card rekap-card rekap-wide" aria-labelledby="rekap-barang">
            <div class="card-head"><div class="card-title"><span class="chip-icon tone-yellow"><x-icon name="package" :size="18" /></span><h2 id="rekap-barang">Per Barang</h2></div></div>
            <div class="table-scroll" tabindex="0" role="region" aria-label="Rekap per barang">
                <table class="data-table rekap-table">
                    <thead><tr><th scope="col">Barang</th><th scope="col" class="ta-r">STO</th><th scope="col" class="ta-r">Qty</th><th scope="col" class="ta-r">Nilai (Rp)</th></tr></thead>
                    <tbody>
                        @foreach($byItem as $item)
                            <tr>
                                <th scope="row">{{ $item->name }}</th>
                                <td class="num ta-r">{{ $rp($item->stos) }}</td>
                                <td class="num ta-r">{{ Format::qty($item->qty) }} {{ $item->unit }}</td>
                                <td class="num ta-r">{{ $rp($item->amount) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    @if($byItem->count() > 1)
                        <tfoot><tr><th scope="row">Total</th><td></td><td class="num ta-r">{{ $ton($byItem->sum('ton')) }} TON</td><td class="num ta-r">{{ $rp($byItem->sum('amount')) }}</td></tr></tfoot>
                    @endif
                </table>
            </div>
        </section>
    </div>
    <p class="hint rekap-note">Tonase dihitung dari barang bersatuan TON dan KG (KG dibagi 1.000). Satuan lain tetap masuk ke STO dan nilai, tetapi tidak ke tonase.</p>
@endif
@endsection
