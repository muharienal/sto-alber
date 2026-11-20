<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('title', 'Dashboard') · {{ config('app.brand') }}</title>
    @include('partials.assets')
</head>
<body>
@php
    $me = auth()->user();
    $initials = collect(preg_split('/\s+/', trim($me->name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') ?: 'U';
    $onDash = request()->routeIs('dashboard');
    $onSto = request()->routeIs('sto.*');
    $onAccount = request()->routeIs('account');
    $onStoPage = request()->routeIs('sto.index', 'sto.preview');
    $onTrash = request()->routeIs('sto.trash');
    $onSto = $onSto && ! $onTrash;
    $isAdmin = $me->isAdmin();
    $onRekap = request()->routeIs('rekap');
    $onUsers = request()->routeIs('users.*');
    $trashCount = $isAdmin ? \App\Models\Sto::onlyTrashed()->count() : 0;
    // Unduh Excel memakai filter yang sedang aktif di Master STO.
    $exportQuery = $onStoPage ? request()->only(\App\Http\Controllers\StoController::FILTER_KEYS) : [];
@endphp
<a class="skip-link" href="#main-content">Langsung ke konten</a>

<div class="app">
    {{-- ===================== SIDEBAR ===================== --}}
    <aside class="sidebar" id="sidebar" aria-label="Navigasi">
        <div class="side-top">
            <a class="brand" href="{{ route('dashboard') }}" aria-label="{{ config('app.brand') }}, ke Dashboard">
                <span class="co-tile"><img src="{{ asset('images/logo-pcs.png') }}" alt="PT Petrokopindo Cipta Selaras"></span>
                <span class="brand-sep"></span>
                @include('partials.lockup')
            </a>
            <button type="button" class="icon-btn side-close" data-nav-close aria-label="Tutup menu"><x-icon name="x" :size="20" /></button>
        </div>

        <nav class="side-nav" aria-label="Menu utama">
            <span class="side-label">Menu</span>
            <a class="side-link {{ $onDash ? 'active' : '' }}" href="{{ route('dashboard') }}" @if($onDash) aria-current="page" @endif><x-icon name="grid" :size="19" />Dashboard</a>
            <a class="side-link {{ $onSto ? 'active' : '' }}" href="{{ route('sto.index') }}" @if($onSto) aria-current="page" @endif><x-icon name="layers" :size="19" />Master STO</a>
            <a class="side-link {{ $onRekap ? 'active' : '' }}" href="{{ route('rekap') }}" @if($onRekap) aria-current="page" @endif><x-icon name="chart" :size="19" />Rekap</a>
            @if($isAdmin)
                <a class="side-link {{ $onTrash ? 'active' : '' }}" href="{{ route('sto.trash') }}" @if($onTrash) aria-current="page" @endif><x-icon name="trash" :size="19" />Sampah @if($trashCount)<span class="side-count">{{ $trashCount }}</span>@endif</a>
            @endif

            <span class="side-label">Pengaturan</span>
            @if($isAdmin)
                <a class="side-link {{ $onUsers ? 'active' : '' }}" href="{{ route('users.index') }}" @if($onUsers) aria-current="page" @endif><x-icon name="users" :size="19" />Pengguna</a>
            @endif
            <a class="side-link {{ $onAccount ? 'active' : '' }}" href="{{ route('account') }}" @if($onAccount) aria-current="page" @endif><x-icon name="key" :size="19" />Akun &amp; Password</a>
        </nav>

        <div class="side-foot">
            <div class="side-user">
                <span class="avatar">{{ $initials }}</span>
                <span class="who"><strong>{{ $me->name }}</strong><small>{{ $me->roleLabel() }}</small></span>
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="icon-btn" title="Keluar" aria-label="Keluar"><x-icon name="logout" :size="18" /></button>
                </form>
            </div>
        </div>
    </aside>
    <div class="side-backdrop" data-nav-close></div>

    {{-- ===================== KONTEN ===================== --}}
    <div class="main">
        <header class="topbar">
            <button type="button" class="icon-btn nav-toggle" data-nav-toggle aria-controls="sidebar" aria-expanded="false" aria-label="Buka menu"><x-icon name="menu" :size="22" /></button>
            <a class="brand topbar-brand" href="{{ route('dashboard') }}">@include('partials.lockup')</a>
            <div class="crumbs">
                <span>{{ config('app.brand') }}</span>
                <x-icon name="chevron-right" :size="14" />
                <strong>@yield('title', 'Dashboard')</strong>
            </div>
            <div class="topbar-actions">
                <span class="today"><x-icon name="calendar" :size="16" />{{ now()->translatedFormat('l, d F Y') }}</span>
            </div>
        </header>

        @if(session('success'))
            <div class="toasts" popover="manual" aria-live="polite">
                <div class="toast toast-success" role="status">
                    <span class="toast-ic"><x-icon name="check-circle" :size="20" /></span>
                    <div class="toast-body">
                        <strong>Berhasil</strong><span>{{ session('success') }}</span>
                        @if(is_array(session('undo')))
                            <form method="post" action="{{ session('undo')['action'] }}" class="toast-undo">
                                @csrf
                                <button type="submit" class="btn btn-secondary btn-sm"><x-icon name="rotate" :size="15" />{{ session('undo')['label'] }}</button>
                            </form>
                        @endif
                    </div>
                    <button type="button" class="toast-x" data-toast-close aria-label="Tutup pemberitahuan"><x-icon name="x" :size="16" /></button>
                    <i class="toast-bar"></i>
                </div>
            </div>
        @endif

        <main class="shell" id="main-content">
            @if($errors->any() && ! $__env->hasSection('local_errors'))
                <div class="alert alert-error" role="alert">
                    <x-icon name="alert" :size="18" />
                    <div>
                        <strong>Periksa kembali isian Anda:</strong>
                        <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="footer">@include('partials.copyright')</footer>
    </div>
</div>
</body>
</html>
