@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
@php
    use App\Support\Format;
    $n = fn ($v) => Format::rupiah($v);
    $hh = (int) now()->format('G');
    $hello = $hh < 11 ? 'Selamat pagi' : ($hh < 15 ? 'Selamat siang' : ($hh < 18 ? 'Selamat sore' : 'Selamat malam'));
    $quickUrl = route('sto.index', ['mode' => 'isi', 'completeness' => 'incomplete']);
    $periodUrl = fn (array $extra = []) => route('sto.index', $period->query() + $extra);
    $missing = array_filter(['SOJ' => $sojEmpty, 'MMJ' => $mmjEmpty]);
@endphp

<div class="page-head">
    <div>
        <span class="eyebrow">{{ $hello }}, {{ \Illuminate\Support\Str::words(auth()->user()->name, 1, '') }}</span>
        <h1>Dashboard</h1>
        <p class="lead">{{ $period->label() }}</p>
    </div>
    <div class="head-actions">
        <a class="btn btn-secondary" href="{{ route('rekap', $period->query()) }}"><x-icon name="chart" :size="16" />Lihat rekap</a>
    </div>
</div>

@include('partials.period', ['periodRoute' => 'dashboard'])

{{-- ===== 4 ANGKA UTAMA ===== --}}
<div class="kpis">
    <a class="kpi" href="{{ $periodUrl() }}">
        <span class="kpi-ico tone-dark"><x-icon name="layers" :size="20" /></span>
        <span class="kpi-l">Total STO</span>
        <span class="kpi-n">{{ $n($total) }}</span>
        <span class="kpi-f">{{ $period->isAll() ? 'Seluruh dokumen' : 'Dalam periode ini' }}</span>
    </a>
    <a class="kpi" href="{{ $periodUrl(['urut' => 'total_desc']) }}">
        <span class="kpi-ico tone-blue"><x-icon name="wallet" :size="20" /></span>
        <span class="kpi-l">Total Nilai</span>
        <span class="kpi-n money"><small>Rp</small>{{ $n($amount) }}</span>
        <span class="kpi-f">Jumlah biaya STO</span>
    </a>
    <a class="kpi" href="{{ route('rekap', $period->query()) }}">
        <span class="kpi-ico tone-yellow"><x-icon name="package" :size="20" /></span>
        <span class="kpi-l">Total Tonase</span>
        <span class="kpi-n">{{ Format::ton($tonnage) }} <small>TON</small></span>
        <span class="kpi-f">@if($otherUnitRows){{ $otherUnitRows }} baris bersatuan lain tidak dihitung @else Barang bersatuan TON/KG @endif</span>
    </a>
    <a class="kpi {{ $incomplete > 0 ? 'is-warn' : 'is-ok' }}" href="{{ $quickUrl }}">
        <span class="kpi-ico {{ $incomplete > 0 ? 'tone-amber' : 'tone-green' }}"><x-icon :name="$incomplete > 0 ? 'file-alert' : 'file-check'" :size="20" /></span>
        <span class="kpi-l">Belum Lengkap</span>
        <span class="kpi-n">{{ $n($incomplete) }}</span>
        <span class="kpi-f">@if($missing){{ collect($missing)->map(fn ($c, $k) => "$k kosong ".$n($c))->implode(' · ') }} @else SOJ &amp; MMJ semua terisi @endif</span>
    </a>
</div>

<div class="grid-main">
    <section class="card">
        <div class="card-head">
            <div><h2>Tonase {{ $chart['daily'] ? 'per Hari' : 'per Bulan' }}</h2><p>Menurut tanggal STO. Arahkan kursor ke batang untuk jumlah STO.</p></div>
        </div>
        <div class="card-body">
            @if(empty($chart['bars']))
                <div class="empty">
                    <span class="chip-icon"><x-icon name="chart" :size="26" /></span>
                    <h3>Belum ada data</h3>
                    <p>Tidak ada STO pada periode ini.</p>
                </div>
            @else
                @php
                    $bars = $chart['bars'];
                    $max = max(0.001, max(array_column($bars, 'ton')));
                    $dense = count($bars) > 14;
                    $every = (int) ceil(count($bars) / 12);
                @endphp
                <div class="bars {{ $dense ? 'is-dense' : '' }}" role="img" aria-label="Grafik tonase {{ $chart['daily'] ? 'per hari' : 'per bulan' }}, {{ $period->label() }}">
                    @foreach($bars as $bar)
                        <div class="bar-col {{ $bar['ton'] <= 0 ? 'is-zero' : '' }}" title="{{ $bar['title'] }}: {{ Format::ton($bar['ton']) }} TON · {{ $bar['stos'] }} STO">
                            <span class="bar-val">{{ $bar['ton'] > 0 ? Format::ton($bar['ton']) : '' }}</span>
                            <div class="bar-track"><div class="bar" style="height:{{ max(2, round($bar['ton'] / $max * 100)) }}%"></div></div>
                            <span class="bar-lbl">{{ ! $dense || $loop->index % $every === 0 || $loop->last ? $bar['label'] : '' }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="card">
        <div class="card-head"><h2>STO Terbaru Diinput</h2><a class="more" href="{{ route('sto.index', ['urut' => 'input_desc']) }}">Lihat semua</a></div>
        @if($latest->isEmpty())
            <div class="empty">
                <span class="chip-icon"><x-icon name="inbox" :size="26" /></span>
                <h3>Belum ada STO</h3>
                <p>Unggah PDF atau tambah manual di Master STO.</p>
            </div>
        @else
            <ul class="recent-list">
                @foreach($latest as $sto)
                    <li>
                        <a class="recent" href="{{ route('sto.index', ['open' => $sto->id]) }}">
                            <span class="chip-icon {{ $sto->activity === 'BONGKAR' ? 'tone-blue' : 'tone-teal' }}"><x-icon name="file" :size="18" /></span>
                            <span class="info">
                                <strong>{{ $sto->number }}</strong>
                                <small>{{ $sto->document_date->format('d/m/Y') }} · {{ $sto->activity }}@if($sto->missingReferences()) · {{ implode(' & ', $sto->missingReferences()) }} kosong @endif</small>
                            </span>
                            <x-icon name="chevron-right" :size="18" class="go" />
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</div>

<div class="grid-2">
    <section class="card">
        <div class="card-head"><div><h2>Jenis Kegiatan</h2><p>Bongkar dan muat, {{ $period->isAll() ? 'seluruh periode' : 'dalam periode ini' }}.</p></div></div>
        <div class="card-body">
            @include('components.donut', ['value' => $bongkar, 'total' => $total, 'label' => 'Bongkar', 'other' => 'Muat', 'color' => '#27272A', 'rest' => '#FACC15'])
        </div>
    </section>
    <section class="card">
        <div class="card-head"><div><h2>Kelengkapan SOJ &amp; MMJ</h2><p>Seluruh STO, tidak terpengaruh periode.</p></div></div>
        <div class="card-body">
            @include('components.donut', ['value' => $allTotal - $incomplete, 'total' => $allTotal, 'label' => 'Lengkap', 'other' => 'Belum lengkap', 'color' => '#16A34A', 'rest' => '#FBBF24'])
        </div>
    </section>
</div>
@endsection
