@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
@php
    $n = fn ($v) => number_format((float) $v, 0, ',', '.');
    $complete = $total - $incomplete;
@endphp

<div class="page-head">
    <div>
        <h1>Dashboard</h1>
        <p class="lead">Ringkasan jumlah, nilai, dan kelengkapan STO.</p>
    </div>
    <a class="btn btn-primary" href="{{ route('sto.index') }}"><x-icon name="upload" :size="18" />Unggah STO</a>
</div>

@if($incomplete > 0)
    <div class="alert alert-warn" role="status">
        <x-icon name="alert" :size="20" />
        <p><strong>{{ $n($incomplete) }} STO belum lengkap.</strong> SOJ atau MMJ masih kosong. <a href="{{ route('sto.index', ['completeness' => 'incomplete']) }}">Lihat daftar</a></p>
    </div>
@endif

<div class="stats">
    <section class="card stat tone-blue" aria-label="Total STO">
        <div class="top"><span class="label">Total STO</span><span class="chip-icon"><x-icon name="layers" :size="20" /></span></div>
        <strong>{{ $n($total) }}</strong>
        <span class="sub">Dokumen tersimpan</span>
    </section>
    <section class="card stat tone-orange" aria-label="Total nilai">
        <div class="top"><span class="label">Total Nilai</span><span class="chip-icon"><x-icon name="wallet" :size="20" /></span></div>
        <strong class="money">Rp {{ $n($amount) }}</strong>
        <span class="sub">Jumlah biaya seluruh STO</span>
    </section>
    <section class="card stat {{ $sojEmpty > 0 ? 'tone-amber' : 'tone-green' }}" aria-label="STO tanpa SOJ">
        <div class="top"><span class="label">SOJ Kosong</span><span class="chip-icon"><x-icon :name="$sojEmpty > 0 ? 'alert' : 'check-circle'" :size="20" /></span></div>
        <strong>{{ $n($sojEmpty) }}</strong>
        <span class="sub">{{ $sojEmpty > 0 ? 'STO belum ada SOJ' : 'Semua sudah terisi' }}</span>
    </section>
    <section class="card stat {{ $mmjEmpty > 0 ? 'tone-amber' : 'tone-green' }}" aria-label="STO tanpa MMJ">
        <div class="top"><span class="label">MMJ Kosong</span><span class="chip-icon"><x-icon :name="$mmjEmpty > 0 ? 'alert' : 'check-circle'" :size="20" /></span></div>
        <strong>{{ $n($mmjEmpty) }}</strong>
        <span class="sub">{{ $mmjEmpty > 0 ? 'STO belum ada MMJ' : 'Semua sudah terisi' }}</span>
    </section>
</div>

<div class="grid-2">
    <section class="card">
        <div class="card-head"><div><h2>Jenis Kegiatan</h2><p>Perbandingan STO bongkar dan muat.</p></div></div>
        @include('components.donut', ['value' => $bongkar, 'total' => $total, 'label' => 'Bongkar', 'other' => 'Muat', 'color' => '#5b6ea9', 'rest' => '#62b3a7'])
    </section>
    <section class="card">
        <div class="card-head"><div><h2>Kelengkapan SOJ &amp; MMJ</h2><p>STO yang SOJ dan MMJ-nya sudah terisi.</p></div></div>
        @include('components.donut', ['value' => $complete, 'total' => $total, 'label' => 'Lengkap', 'other' => 'Belum lengkap', 'color' => '#4fa583', 'rest' => '#f4d58c'])
    </section>
</div>

<div class="grid-main">
    <section class="card">
        <div class="card-head"><div><h2>STO Masuk 7 Hari Terakhir</h2><p>Jumlah STO tersimpan per hari.</p></div></div>
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
    </section>

    <section class="card">
        <div class="card-head"><div><h2>STO Terbaru</h2><p>5 STO terakhir yang disimpan.</p></div></div>
        @if($latest->isEmpty())
            <p>Belum ada STO. Unggah PDF atau tambah manual di <a href="{{ route('sto.index') }}">Master STO</a>.</p>
        @else
            <div class="recent-list">
                @foreach($latest as $sto)
                    <a class="recent" href="{{ route('sto.index', ['open' => $sto->id]) }}">
                        <span class="chip-icon {{ $sto->activity === 'BONGKAR' ? 'tone-blue' : 'tone-teal' }}"><x-icon name="file" :size="18" /></span>
                        <span class="info">
                            <strong>{{ $sto->number }}</strong>
                            <small>{{ $sto->document_date->format('d/m/Y') }} · {{ $sto->activity }}@if($sto->missingReferences()) · {{ implode(' & ', $sto->missingReferences()) }} kosong @endif</small>
                        </span>
                        <x-icon name="chevron-right" :size="18" class="go" />
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection
