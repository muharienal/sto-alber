@extends('layouts.app')
@section('title', 'Master STO')
@section('local_errors', '1')

@section('content')
@php
    $filterKeys = \App\Http\Controllers\StoController::FILTER_KEYS;
    $filters = request()->only($filterKeys);
    $hasFilter = collect($filters)->except('urut')->filter(fn ($v) => filled($v))->isNotEmpty();
    $rupiah = fn ($v) => number_format((float) $v, 0, ',', '.');
    $qty = fn ($v) => \App\Support\Format::qty($v);
    $date = fn ($v) => \Illuminate\Support\Carbon::parse($v)->format('d/m/Y');
    $createSubmitted = old('_modal') === 'create-sto';
    $thisMonth = [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()];
    $lastMonth = [now()->subMonthNoOverflow()->startOfMonth()->toDateString(), now()->subMonthNoOverflow()->endOfMonth()->toDateString()];
    $periodLink = fn (array $range) => route('sto.index', array_merge($filters, ['dari' => $range[0], 'sampai' => $range[1]], $quick ? ['mode' => 'isi'] : []));
    $isPeriod = fn (array $range) => request('dari') === $range[0] && request('sampai') === $range[1];
@endphp

<div class="page-head">
    <div>
        <h1>Master STO</h1>
        <p class="lead">Cari, periksa, dan lengkapi SOJ/MMJ setiap STO.</p>
    </div>
    <div class="head-actions">
        <button type="button" class="btn btn-secondary" data-dialog-open="create-sto"><x-icon name="plus" :size="16" />Tambah STO</button>
        <button type="button" class="btn btn-cta" data-dialog-open="upload-pdf"><x-icon name="upload" :size="16" />Unggah PDF</button>
    </div>
</div>

@if($failedRows->isNotEmpty())
    @include('sto._failed')
@endif


<section class="card list-card {{ $quick ? 'is-quick' : '' }}" aria-labelledby="list-title">
<div class="card-head">
    <div>
        <h2 id="list-title">{{ $quick ? 'Isi cepat SOJ/MMJ' : 'Daftar STO' }} <span class="count-pill">{{ number_format($stos->total(), 0, ',', '.') }} STO</span></h2>
        @if($quick)<p>Hanya kolom yang diubah yang disimpan.</p>@endif
    </div>
    <div class="head-actions">
        @if($quick)
            <a class="btn btn-secondary btn-sm" href="{{ route('sto.index', $filters) }}" data-quick-exit><x-icon name="x" :size="16" />Selesai isi cepat</a>
        @else
            <a class="btn {{ $incompleteCount ? 'btn-cta' : 'btn-secondary' }} btn-sm" href="{{ route('sto.index', array_merge($filters, ['mode' => 'isi'], $hasFilter ? [] : ['completeness' => 'incomplete'])) }}"><x-icon name="edit" :size="16" />Isi SOJ/MMJ cepat @if($incompleteCount)<span class="btn-count">{{ number_format($incompleteCount, 0, ',', '.') }} belum lengkap</span>@endif</a>
        @endif
        <a class="btn btn-secondary btn-sm" href="{{ route('sto.export', $filters) }}" title="{{ $hasFilter ? 'Unduh STO sesuai filter yang aktif' : 'Unduh semua STO' }}"><x-icon name="download" :size="16" />{{ $hasFilter ? 'Unduh Excel (hasil filter)' : 'Unduh Excel' }}</a>
    </div>
</div>

<form class="card-body filters" method="get" action="{{ route('sto.index') }}" role="search">
    @if($quick)<input type="hidden" name="mode" value="isi">@endif
    <div class="filter-row">
        <div class="input-icon">
            <x-icon name="search" :size="18" />
            <input class="input" type="search" name="q" value="{{ request('q') }}" placeholder="Cari nomor, barang, asal, tujuan, SOJ, MMJ" aria-label="Cari nomor STO, nama barang, asal, tujuan, SOJ, atau MMJ" maxlength="100">
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
    </div>
    <div class="filter-row">
        <label class="period">
            <span>Tanggal STO</span>
            <input class="input" type="date" name="dari" value="{{ request('dari') }}" aria-label="Tanggal STO dari">
            <span class="sep">s/d</span>
            <input class="input" type="date" name="sampai" value="{{ request('sampai') }}" aria-label="Tanggal STO sampai">
        </label>
        <span class="period-chips">
            <a class="chip {{ $isPeriod($thisMonth) ? 'is-on' : '' }}" href="{{ $periodLink($thisMonth) }}">Bulan ini</a>
            <a class="chip {{ $isPeriod($lastMonth) ? 'is-on' : '' }}" href="{{ $periodLink($lastMonth) }}">Bulan lalu</a>
        </span>
        <select class="select" name="urut" aria-label="Urutkan">
            @foreach($sorts as $key => $label)
                <option value="{{ $key }}" @selected((request('urut') ?: 'tanggal_desc') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary"><x-icon name="search" :size="16" />Cari</button>
        @if($hasFilter)
            <a class="btn btn-ghost" href="{{ route('sto.index', $quick ? ['mode' => 'isi'] : []) }}"><x-icon name="rotate" :size="16" />Reset</a>
        @endif
    </div>
</form>

@if($stos->isEmpty())
    <div class="list-empty">
        <div class="empty">
            <span class="chip-icon"><x-icon :name="$quick ? 'check-circle' : 'inbox'" :size="26" /></span>
            @if($quick && request('completeness') === 'incomplete')
                <h3>Semua SOJ/MMJ sudah lengkap</h3>
                <p>Tidak ada STO yang perlu diisi pada filter ini.</p>
            @elseif($hasFilter)
                <h3>Tidak ada STO yang cocok</h3>
                <p>Ubah kata kunci, periode, atau reset filter.</p>
            @else
                <h3>Belum ada STO</h3>
                <p>Unggah PDF atau tambah STO manual lewat tombol di kanan atas.</p>
            @endif
        </div>
    </div>
@elseif($quick)
    @include('sto._quick')
    {{ $stos->links() }}
@else
    @include('sto._table')
    {{ $stos->links() }}
@endif
</section>

{{-- ============ Modal tambah STO ============ --}}
<dialog class="modal modal-lg is-editing" id="create-sto" data-always-edit aria-labelledby="create-sto-title" @if($createSubmitted || (request()->boolean('tambah') && ! isset($batch))) data-autoopen @endif>
    <div class="modal-head">
        <span class="chip-icon tone-orange"><x-icon name="plus" :size="20" /></span>
        <div class="titles">
            <h2 id="create-sto-title">Tambah STO</h2>
            <p>SOJ dan MMJ boleh diisi nanti.</p>
        </div>
        <button type="button" class="icon-btn" data-dialog-close aria-label="Tutup"><x-icon name="x" :size="20" /></button>
    </div>
    <div class="modal-body">
        {{-- Diisi JS saat "Tambah manual" dari STO gagal dibaca. --}}
        <div class="alert alert-info prefill-note" data-prefill-note @if(! old('import_batch_id')) hidden @endif>
            <x-icon name="info" :size="18" />
            <p>Terisi dari <b data-prefill-source>{{ old('_prefill_source') }}</b>. Kolom yang gagal dibaca masih kosong. <a href="{{ old('_prefill_pdf') }}" target="_blank" rel="noopener" data-prefill-pdf>Buka halaman PDF</a> untuk melengkapinya.</p>
        </div>
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

{{-- ============ Detail STO: hanya yang harus langsung terbuka; lainnya dimuat saat diklik ============ --}}
@foreach($dialogStos as $dialogSto)
    @include('sto._modal', ['sto' => $dialogSto])
@endforeach
<template id="sto-panel-url" data-url="{{ url('/sto/__ID__/panel') }}"></template>

@include('sto._upload')

@isset($batch)
    @include('sto._preview')
@endisset
@endsection
