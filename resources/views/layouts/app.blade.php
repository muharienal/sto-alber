<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('title', 'Dashboard') · STO Alber</title>
    @include('partials.assets')
</head>
<body>
<a class="skip-link" href="#main-content">Langsung ke konten</a>

<header class="topbar">
    <div class="topbar-in container">
        <a class="brand" href="{{ route('dashboard') }}" aria-label="STO Alber, ke Dashboard">
            <x-logo :size="40" class="brand-logo" />
            <span class="brand-name">STO <b>Alber</b></span>
        </a>

        <nav class="mainnav" aria-label="Menu utama">
            <a class="navlink {{ request()->routeIs('dashboard') ? 'is-active' : '' }}" href="{{ route('dashboard') }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif>
                <x-icon name="grid" :size="18" /><span>Dashboard</span>
            </a>
            <a class="navlink {{ request()->routeIs('sto.*') ? 'is-active' : '' }}" href="{{ route('sto.index') }}" @if(request()->routeIs('sto.*')) aria-current="page" @endif>
                <x-icon name="layers" :size="18" /><span>Master STO</span>
            </a>
        </nav>

        <details class="usermenu">
            <summary aria-label="Menu akun">
                <span class="avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                <span class="uname">{{ auth()->user()->name }}</span>
                <x-icon name="chevron-down" :size="16" class="chev" />
            </summary>
            <div class="menu-pop">
                <div class="who">
                    <strong>{{ auth()->user()->name }}</strong>
                    <span>{{ auth()->user()->username }}</span>
                </div>
                <a href="{{ route('account') }}"><x-icon name="key" :size="18" />Akun</a>
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="is-danger"><x-icon name="logout" :size="18" />Keluar</button>
                </form>
            </div>
        </details>
    </div>
</header>

@if(session('success'))
    <div class="toast-stack">
        <div class="toast" role="status">
            <x-icon name="check-circle" :size="22" />
            <p>{{ session('success') }}</p>
            <button type="button" class="icon-btn" data-toast-close aria-label="Tutup pemberitahuan"><x-icon name="x" :size="18" /></button>
        </div>
    </div>
@endif

<main class="container page" id="main-content">
    @if($errors->any() && ! $__env->hasSection('local_errors'))
        <div class="alert alert-danger" role="alert">
            <x-icon name="alert" :size="20" />
            <div>
                <strong>Periksa kembali isian Anda:</strong>
                <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        </div>
    @endif

    @yield('content')
</main>

<footer class="footer container">@include('partials.copyright')</footer>
</body>
</html>
