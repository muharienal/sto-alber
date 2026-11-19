@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
@php
    $n = fn ($v) => number_format((float) $v, 0, ',', '.');
    $complete = $total - $incomplete;
    $hh = (int) now()->format('G');
    $hello = $hh < 11 ? 'Selamat pagi' : ($hh < 15 ? 'Selamat siang' : ($hh < 18 ? 'Selamat sore' : 'Selamat malam'));
    $incompleteUrl = route('sto.index', ['completeness' => 'incomplete']);
@endphp

<div class="page-head">
    <div>
        <span class="eyebrow">{{ $hello }}, {{ \Illuminate\Support\Str::words(auth()->user()->name, 1, '') }}</span>
        <h1>Dashboard</h1>
        <p class="lead">Ringkasan jumlah, nilai, dan kelengkapan dokumen STO.</p>
    </div>
    <div class="head-actions">
        <a class="btn btn-secondary" href="{{ route('sto.index', ['tambah' => 1]) }}"><x-icon name="plus" :size="16" />Tambah STO</a>
        <a class="btn btn-cta" href="{{ route('sto.index') }}#unggah"><x-icon name="upload" :size="16" />Unggah PDF</a>
    </div>
</div>

{{-- ===== 4 ANGKA UTAMA ===== --}}
<div class="kpis">
    <a class="kpi" href="{{ route('sto.index') }}">
        <span class="kpi-ico tone-dark"><x-icon name="layers" :size="20" /></span>
        <span class="kpi-l">Total STO</span>
        <span class="kpi-n">{{ $n($total) }}</span>
        <span class="kpi-f">Dokumen tersimpan</span>
    </a>
    <a class="kpi" href="{{ route('sto.index') }}">
        <span class="kpi-ico tone-blue"><x-icon name="wallet" :size="20" /></span>
        <span class="kpi-l">Total Nilai</span>
        <span class="kpi-n money"><small>Rp</small>{{ $n($amount) }}</span>
        <span class="kpi-f">Jumlah biaya seluruh STO</span>
    </a>
    <a class="kpi {{ $sojEmpty > 0 ? 'is-warn' : 'is-ok' }}" href="{{ $incompleteUrl }}">
        <span class="kpi-ico {{ $sojEmpty > 0 ? 'tone-amber' : 'tone-green' }}"><x-icon :name="$sojEmpty > 0 ? 'file-alert' : 'file-check'" :size="20" /></span>
        <span class="kpi-l">SOJ Kosong</span>
        <span class="kpi-n">{{ $n($sojEmpty) }}</span>
        <span class="kpi-f">{{ $sojEmpty > 0 ? 'Perlu dilengkapi' : 'Semua sudah terisi' }}</span>
    </a>
    <a class="kpi {{ $mmjEmpty > 0 ? 'is-warn' : 'is-ok' }}" href="{{ $incompleteUrl }}">
        <span class="kpi-ico {{ $mmjEmpty > 0 ? 'tone-amber' : 'tone-green' }}"><x-icon :name="$mmjEmpty > 0 ? 'file-alert' : 'file-check'" :size="20" /></span>
        <span class="kpi-l">MMJ Kosong</span>
        <span class="kpi-n">{{ $n($mmjEmpty) }}</span>
        <span class="kpi-f">{{ $mmjEmpty > 0 ? 'Perlu dilengkapi' : 'Semua sudah terisi' }}</span>
    </a>
</div>

@if($incomplete > 0)
    <div class="alert alert-warn" role="status">
        <x-icon name="alert" :size="18" />
        <p><strong>{{ $n($incomplete) }} STO belum lengkap.</strong> SOJ atau MMJ masih kosong. <a href="{{ $incompleteUrl }}">Lihat daftar</a></p>
    </div>
@endif

<div class="grid-2">
    <section class="card">
        <div class="card-head"><div><h2>Jenis Kegiatan</h2><p>Perbandingan STO bongkar dan muat.</p></div></div>
        <div class="card-body">
            @include('components.donut', ['value' => $bongkar, 'total' => $total, 'label' => 'Bongkar', 'other' => 'Muat', 'color' => '#27272A', 'rest' => '#FACC15'])
        </div>
    </section>
    <section class="card">
        <div class="card-head"><div><h2>Kelengkapan SOJ &amp; MMJ</h2><p>STO yang SOJ dan MMJ-nya sudah terisi.</p></div></div>
        <div class="card-body">
            @include('components.donut', ['value' => $complete, 'total' => $total, 'label' => 'Lengkap', 'other' => 'Belum lengkap', 'color' => '#16A34A', 'rest' => '#FBBF24'])
        </div>
    </section>
</div>

<div class="grid-main">
    <section class="card">
        <div class="card-head"><div><h2>STO Masuk 7 Hari Terakhir</h2><p>Jumlah STO tersimpan per hari.</p></div></div>
        <div class="card-body">
            @php $max = max(1, max(array_column($days, 'count'))); @endphp
            <div class="bars" role="img" aria-label="Grafik jumlah STO tersimpan per hari, 7 hari terakhir">
                @foreach($days as $day)
                    <div class="bar-col {{ $loop->last ? 'is-today' : '' }} {{ $day['count'] === 0 ? 'is-zero' : '' }}">
                        <span class="bar-val">{{ $day['count'] }}</span>
                        <div class="bar-track"><div class="bar" style="height:{{ max(3, round($day['count'] / $max * 100)) }}%"></div></div>
                        <span class="bar-lbl">{{ $day['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="card">
        <div class="card-head"><h2>STO Terbaru</h2><a class="more" href="{{ route('sto.index') }}">Lihat semua</a></div>
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
@endsection
