# STO Alber — Full code pembaruan: warna lembut + logo

Berkas yang berubah atau baru pada pembaruan ini. Salin isi tiap blok ke path yang tertulis di judulnya.

Berkas biner (dibuat dari `public/favicon.svg`, sudah ada di ZIP): `public/favicon.ico` (16/32/48 px), `public/apple-touch-icon.png` (180 px), `public/logo-512.png` (512 px).

## resources/views/components/logo.blade.php

```blade
{{--
    Logo STO Alber: peti barang (stok) + panah (transport) di atas kotak navy.
    Pakai: <x-logo :size="38" />  — salinan berkas yang sama ada di public/favicon.svg.
--}}
@props(['size' => 38])
@php $gid = 'sto-logo-'.\Illuminate\Support\Str::random(6); @endphp
<svg {{ $attributes->merge(['class' => 'logo-mark']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 64 64" role="img" aria-hidden="true" focusable="false">
    <defs>
        <linearGradient id="{{ $gid }}" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0" stop-color="#4a5d9c"/>
            <stop offset="1" stop-color="#2b3a6b"/>
        </linearGradient>
    </defs>
    <rect width="64" height="64" rx="16" fill="url(#{{ $gid }})"/>
    <path d="M32 9 50 18.5v19L32 47 14 37.5v-19z" fill="#fff"/>
    <path d="M32 9 50 18.5 32 28 14 18.5z" fill="#f2a46b"/>
    <path d="M32 28v19" stroke="#d5dbee" stroke-width="2.4"/>
    <path d="M23 13.7 41 23.2v6.6" fill="none" stroke="#fff" stroke-width="2.6" stroke-linejoin="round"/>
    <path d="M15 54.5h30" stroke="#f2a46b" stroke-width="4.2" stroke-linecap="round"/>
    <path d="M41 49.5 47.5 54.5 41 59.5" fill="none" stroke="#f2a46b" stroke-width="4.2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
```

## public/favicon.svg

```svg
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">
  <defs>
    <linearGradient id="sto-bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="#4a5d9c"/>
      <stop offset="1" stop-color="#2b3a6b"/>
    </linearGradient>
  </defs>
  <rect width="64" height="64" rx="16" fill="url(#sto-bg)"/>
  <path d="M32 9 50 18.5v19L32 47 14 37.5v-19z" fill="#fff"/>
  <path d="M32 9 50 18.5 32 28 14 18.5z" fill="#f2a46b"/>
  <path d="M32 28v19" stroke="#d5dbee" stroke-width="2.4"/>
  <path d="M23 13.7 41 23.2v6.6" fill="none" stroke="#fff" stroke-width="2.6" stroke-linejoin="round"/>
  <path d="M15 54.5h30" stroke="#f2a46b" stroke-width="4.2" stroke-linecap="round"/>
  <path d="M41 49.5 47.5 54.5 41 59.5" fill="none" stroke="#f2a46b" stroke-width="4.2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
```

## resources/views/partials/assets.blade.php

```blade
<meta name="color-scheme" content="light">
<link rel="icon" href="{{ asset('favicon.svg') }}?v=2" type="image/svg+xml">
<link rel="alternate icon" href="{{ asset('favicon.ico') }}?v=2" sizes="any">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
<meta name="theme-color" content="#33447a">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) }}">
<script src="{{ asset('js/app.js') }}?v={{ @filemtime(public_path('js/app.js')) }}" defer></script>
```

## resources/views/layouts/app.blade.php

```blade
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
```

## resources/views/auth/login.blade.php

```blade
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Masuk · STO Alber</title>
    @include('partials.assets')
</head>
<body>
<div class="auth">
    <main class="auth-main">
        <a class="brand" href="{{ route('login') }}" aria-label="STO Alber">
            <x-logo :size="40" class="brand-logo" />
            <span class="brand-name">STO <b>Alber</b></span>
        </a>

        <div class="auth-form">
            <h1>Masuk</h1>
            <p>Gunakan username dan password Anda.</p>

            @if($errors->any())
                <div class="alert alert-danger" role="alert">
                    <x-icon name="alert" :size="20" />
                    <p>{{ $errors->first() }}</p>
                </div>
            @endif

            <form method="post" action="{{ route('login.submit') }}" data-loading>
                @csrf
                <div class="field">
                    <label class="field-label" for="username">Username</label>
                    <div class="input-icon">
                        <x-icon name="user" :size="18" />
                        <input class="input" id="username" name="username" value="{{ old('username') }}" autocomplete="username" required autofocus placeholder="Masukkan username">
                    </div>
                </div>
                <div class="field">
                    <label class="field-label" for="password">Password</label>
                    <div class="input-icon has-reveal">
                        <x-icon name="lock" :size="18" />
                        <input class="input" id="password" type="password" name="password" autocomplete="current-password" required placeholder="Masukkan password">
                        <button type="button" class="icon-btn reveal" data-toggle-password="password" aria-label="Tampilkan password" aria-pressed="false">
                            <x-icon name="eye" :size="18" class="icon eye-on" /><x-icon name="eye-off" :size="18" class="icon eye-off" />
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-lg btn-block" data-submit data-loading-text="Memproses…">Masuk</button>
            </form>
        </div>

        <p class="auth-copy">@include('partials.copyright')</p>
    </main>

    <aside class="auth-panel" aria-hidden="true">
        <svg class="auth-art" viewBox="0 0 480 330" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="8" y="296" width="464" height="10" rx="5" fill="#fff" opacity=".18"/>
            {{-- palet kiri --}}
            <rect x="40" y="276" width="170" height="18" rx="4" fill="#fff" opacity=".35"/>
            <rect x="48" y="226" width="74" height="46" rx="9" fill="#fff" opacity=".92"/>
            <rect x="128" y="226" width="74" height="46" rx="9" fill="#fff" opacity=".7"/>
            <rect x="48" y="176" width="74" height="46" rx="9" fill="#fff" opacity=".6"/>
            <rect x="128" y="176" width="74" height="46" rx="9" fill="#fff" opacity=".92"/>
            <rect x="88" y="126" width="74" height="46" rx="9" fill="#fff" opacity=".8"/>
            {{-- palet kanan --}}
            <rect x="260" y="276" width="190" height="18" rx="4" fill="#fff" opacity=".35"/>
            <rect x="268" y="226" width="80" height="46" rx="9" fill="#fff" opacity=".7"/>
            <rect x="354" y="226" width="80" height="46" rx="9" fill="#fff" opacity=".92"/>
            <rect x="268" y="176" width="80" height="46" rx="9" fill="#fff" opacity=".92"/>
            <rect x="354" y="176" width="80" height="46" rx="9" fill="#fff" opacity=".6"/>
            <rect x="268" y="126" width="80" height="46" rx="9" fill="#fff" opacity=".6"/>
            <rect x="354" y="126" width="80" height="46" rx="9" fill="#fff" opacity=".85"/>
            <rect x="310" y="76" width="80" height="46" rx="9" fill="#fff" opacity=".92"/>
            {{-- tanda dokumen --}}
            <rect x="196" y="26" width="92" height="64" rx="12" fill="#e99a68"/>
            <path d="M222 48h40M222 60h40M222 72h24" stroke="#fff" stroke-width="4" stroke-linecap="round"/>
        </svg>
        <span class="auth-tag">STO Alat Berat</span>
        <h2>Data STO<br>rapi di satu tempat.</h2>
        <p>Impor dari PDF, periksa, lalu simpan.</p>
    </aside>
</div>
</body>
</html>
```

## resources/views/errors/layout.blade.php

```blade
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('code') · STO Alber</title>
    @include('partials.assets')
</head>
<body>
<main class="error-wrap">
    <section class="card error-card">
        <a class="brand" href="{{ url('/') }}" aria-label="STO Alber">
            <x-logo :size="40" class="brand-logo" />
            <span class="brand-name">STO <b>Alber</b></span>
        </a>
        <div class="error-code">@yield('code')</div>
        <h2>@yield('message')</h2>
        <p>@yield('detail')</p>
        <a class="btn btn-primary" href="{{ url('/') }}"><x-icon name="arrow-left" :size="18" />Kembali</a>
    </section>
</main>
<footer class="footer container">@include('partials.copyright')</footer>
</body>
</html>
```

## resources/views/dashboard.blade.php

```blade
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
```

## public/css/app.css

```css
/* ==========================================================================
   STO Alber — design system
   Palet diselaraskan dengan Cuci Alber: navy (identitas), oranye (aksi),
   amber (penanda), latar gradasi hangat.
   Font : Plus Jakarta Sans (fallback ke font sistem bila offline).
   ========================================================================== */

:root {
  --bg: #f1eff2;
  --surface: #ffffff;
  --ink: #0f1c2e;
  --ink-2: #44546a;
  --ink-3: #74839a;
  --line: #e2e8f0;
  --line-soft: #eef2f7;

  /* identitas: navy (header Cuci Alber) */
  --navy: #2b3a6b;
  --navy-2: #4a5d9c;
  --navy-soft: #edf0f8;
  --navy-glass: linear-gradient(105deg, rgba(43, 58, 107, .93) 0%, rgba(74, 93, 156, .88) 100%);
  /* aksi: oranye (tombol Lanjut Cuci Alber) */
  --primary: #e58b52;
  --primary-d: #cf7440;
  --primary-soft: #fdf1e9;
  --accent-line: rgba(229, 139, 82, .6);
  --teal: #2f9a8b;
  --teal-soft: #e2f4f1;
  --green: #2f8a66;
  --green-soft: #e3f4ec;
  --amber: #9a6a12;
  --amber-soft: #fff7e2;
  --rose: #bd2d4b;
  --rose-soft: #fde7ec;
  --slate-soft: #eaeef4;

  --shadow-sm: 0 1px 2px rgba(15, 28, 46, .05);
  --shadow-md: 0 14px 34px -18px rgba(43, 58, 107, .22);
  --shadow-lg: 0 40px 90px -28px rgba(30, 40, 80, .38);

  --r-sm: 8px;
  --r-md: 12px;
  --r-lg: 18px;
  --r-xl: 22px;

  --font: "Plus Jakarta Sans", ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  --topbar-h: 66px;
}

/* ---------- Reset & dasar ---------- */
*, *::before, *::after { box-sizing: border-box; }
html { background: var(--bg); -webkit-text-size-adjust: 100%; scroll-padding-top: calc(var(--topbar-h) + 16px); }
body {
  margin: 0;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: linear-gradient(115deg, #f8eee8 0%, #f4f2f4 45%, #e9ecf4 100%);
  color: var(--ink);
  font: 400 14px/1.6 var(--font);
  -webkit-font-smoothing: antialiased;
}
body:has(dialog[open]) { overflow: hidden; }
h1, h2, h3, p, ul, ol, dl, dd, figure { margin: 0; }
h1 { font-size: 28px; line-height: 1.2; font-weight: 800; letter-spacing: -.02em; }
h2 { font-size: 18px; line-height: 1.3; font-weight: 700; letter-spacing: -.01em; }
h3 { font-size: 15px; font-weight: 700; }
p { color: var(--ink-2); }
a { color: var(--navy-2); text-decoration: none; }
a:hover { color: var(--primary-d); }
button, input, select { font: inherit; color: inherit; }
button { cursor: pointer; }
ul, ol { padding: 0; list-style: none; }
svg { flex: none; }
.icon { display: inline-block; vertical-align: middle; }
:focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; }
.topbar :focus-visible, .page-head :focus-visible, .auth-panel :focus-visible { outline-color: #ffd28a; }
.sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }
.skip-link { position: absolute; left: 12px; top: -80px; z-index: 100; background: var(--surface); padding: 10px 16px; border-radius: var(--r-sm); box-shadow: var(--shadow-md); font-weight: 600; }
.skip-link:focus { top: 12px; }
.num { font-variant-numeric: tabular-nums; }

/* ---------- Layout ---------- */
.container { width: 100%; max-width: 1400px; margin-inline: auto; padding-inline: 28px; }
.page { flex: 1; padding-top: 32px; padding-bottom: 40px; }
.page > * + * { margin-top: 24px; }
.page-head {
  display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;
  padding: 26px 28px; border-radius: var(--r-xl); color: #fff;
  background: var(--navy-glass);
  border-bottom: 3px solid var(--accent-line); box-shadow: 0 16px 36px -24px rgba(43, 58, 107, .55);
}
.page-head h1 { color: #fff; }
.page-head .lead { margin-top: 6px; max-width: 62ch; color: rgba(255, 255, 255, .82); }
.page-head .btn-primary { box-shadow: 0 8px 18px -12px rgba(0, 0, 0, .35); }
.section-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
.section-head p { margin-top: 2px; }
.footer { padding-block: 22px 28px; text-align: center; color: var(--ink-3); font-size: 12.5px; }

/* ---------- Topbar ---------- */
.topbar {
  position: sticky; top: 0; z-index: 40;
  background: var(--navy-glass);
  backdrop-filter: saturate(1.3) blur(12px);
  -webkit-backdrop-filter: saturate(1.3) blur(12px);
  border-bottom: 2px solid var(--accent-line);
  box-shadow: 0 8px 24px -18px rgba(30, 40, 80, .5);
}
.topbar-in { height: var(--topbar-h); display: flex; align-items: center; gap: 28px; }
.brand { display: inline-flex; align-items: center; gap: 12px; color: var(--ink); font-size: 18px; font-weight: 800; letter-spacing: -.02em; }
.brand:hover { color: var(--ink); }
.topbar .brand, .topbar .brand:hover { color: #fff; }
.brand-logo { display: block; flex: none; border-radius: 11px; box-shadow: 0 6px 16px -8px rgba(30, 40, 80, .55); }
.topbar .brand-logo { box-shadow: 0 0 0 1px rgba(255, 255, 255, .22), 0 6px 16px -8px rgba(0, 0, 0, .4); }
.brand-name b { font-weight: 800; color: var(--primary-d); }
.topbar .brand-name b { color: #f6b88c; }
.mainnav { display: flex; align-items: center; gap: 4px; margin-right: auto; }
.navlink { white-space: nowrap; display: inline-flex; align-items: center; gap: 8px; height: 38px; padding: 0 14px; border-radius: 10px; color: rgba(255, 255, 255, .74); font-weight: 600; transition: background .15s, color .15s; }
.navlink:hover { background: rgba(255, 255, 255, .1); color: #fff; }
.navlink.is-active { background: rgba(255, 255, 255, .14); color: #fff; box-shadow: inset 0 -2px 0 var(--accent-line); }
.usermenu { position: relative; }
.usermenu summary { list-style: none; display: inline-flex; align-items: center; gap: 10px; height: 42px; padding: 0 12px 0 5px; border: 1px solid rgba(255, 255, 255, .22); border-radius: 999px; background: rgba(255, 255, 255, .08); color: #fff; cursor: pointer; font-weight: 600; transition: border-color .15s, box-shadow .15s; }
.usermenu summary::-webkit-details-marker { display: none; }
.usermenu summary:hover { border-color: rgba(255, 255, 255, .45); }
.usermenu[open] summary { border-color: var(--accent-line); box-shadow: 0 0 0 3px rgba(229, 139, 82, .22); }
.usermenu summary .chev { color: rgba(255, 255, 255, .7); transition: transform .15s; }
.usermenu[open] summary .chev { transform: rotate(180deg); }
.avatar { display: grid; place-items: center; width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #e99a68, #f2b98f); color: #fff; font-size: 13px; font-weight: 800; }
.avatar-lg { width: 64px; height: 64px; font-size: 24px; }
.menu-pop { position: absolute; right: 0; top: calc(100% + 8px); min-width: 220px; padding: 6px; background: var(--surface); border: 1px solid var(--line); border-radius: var(--r-md); box-shadow: var(--shadow-md); z-index: 50; }
.menu-pop .who { padding: 10px 12px 8px; border-bottom: 1px solid var(--line-soft); margin-bottom: 6px; }
.menu-pop .who strong { display: block; color: var(--ink); }
.menu-pop .who span { font-size: 12.5px; color: var(--ink-3); }
.menu-pop a, .menu-pop button { display: flex; width: 100%; align-items: center; gap: 10px; padding: 9px 12px; border: 0; background: none; border-radius: 8px; color: var(--ink-2); font-weight: 600; text-align: left; }
.menu-pop a:hover, .menu-pop button:hover { background: var(--slate-soft); color: var(--ink); }
.menu-pop .is-danger { color: var(--rose); }
.menu-pop .is-danger:hover { background: var(--rose-soft); color: var(--rose); }
.menu-pop form { margin: 0; }

/* ---------- Tombol ---------- */
.btn {
  display: inline-flex; align-items: center; justify-content: center; gap: 8px;
  min-height: 42px; padding: 0 18px; border: 1px solid transparent; border-radius: 10px;
  background: var(--primary); color: #fff; font-weight: 650; line-height: 1.2; text-align: center; white-space: nowrap;
  transition: background .15s, border-color .15s, box-shadow .15s, transform .05s;
}
.btn:hover { background: var(--primary-d); color: #fff; }
.btn:active { transform: translateY(1px); }
.btn:disabled, .btn[aria-disabled="true"] { opacity: .55; cursor: not-allowed; transform: none; }
.btn-primary { box-shadow: 0 8px 18px -12px rgba(229, 139, 82, .7); }
.btn-secondary { background: var(--surface); color: var(--ink); border-color: var(--line); box-shadow: var(--shadow-sm); }
.btn-secondary:hover { background: #fbfbfd; color: var(--navy-2); border-color: #c7cee2; }
.btn-navy { background: var(--navy-2); }
.btn-navy:hover { background: var(--navy); }
.btn-ghost { background: transparent; color: var(--ink-2); }
.btn-ghost:hover { background: var(--slate-soft); color: var(--ink); }
.btn-danger { background: var(--rose); }
.btn-danger:hover { background: #a02039; }
.btn-danger-soft { background: var(--rose-soft); color: var(--rose); }
.btn-danger-soft:hover { background: #fbd5de; color: #a02039; }
.btn-sm { min-height: 34px; padding: 0 12px; font-size: 13px; border-radius: 8px; }
.btn-lg { min-height: 48px; padding: 0 24px; font-size: 15px; border-radius: 12px; }
.btn-block { width: 100%; }
.icon-btn { display: inline-grid; place-items: center; width: 38px; height: 38px; border: 0; border-radius: 10px; background: transparent; color: var(--ink-3); transition: background .15s, color .15s; }
.icon-btn:hover { background: var(--slate-soft); color: var(--ink); }
.spinner { width: 16px; height: 16px; border-radius: 50%; border: 2px solid rgba(255, 255, 255, .4); border-top-color: #fff; animation: spin .7s linear infinite; }
.btn-secondary .spinner, .btn-ghost .spinner { border-color: rgba(15, 28, 46, .2); border-top-color: var(--ink); }
@keyframes spin { to { transform: rotate(360deg); } }

/* ---------- Kartu ---------- */
.card { background: var(--surface); border: 1px solid var(--line); border-radius: var(--r-lg); padding: 24px; box-shadow: var(--shadow-sm); min-width: 0; }
.card-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 18px; }
.card-head p { margin-top: 2px; font-size: 13px; }
.card-title { display: flex; align-items: center; gap: 10px; }
.chip-icon { display: grid; place-items: center; width: 38px; height: 38px; border-radius: 11px; background: var(--tone-soft, var(--navy-soft)); color: var(--tone, var(--navy-2)); flex: none; }
.tone-blue { --tone: var(--navy-2); --tone-soft: var(--navy-soft); }
.tone-orange { --tone: var(--primary-d); --tone-soft: var(--primary-soft); }
.tone-teal { --tone: var(--teal); --tone-soft: var(--teal-soft); }
.tone-green { --tone: var(--green); --tone-soft: var(--green-soft); }
.tone-amber { --tone: var(--amber); --tone-soft: var(--amber-soft); }
.tone-rose { --tone: var(--rose); --tone-soft: var(--rose-soft); }

/* ---------- Form ---------- */
.field { display: block; }
.field-label { display: block; margin-bottom: 7px; color: var(--ink); font-weight: 650; font-size: 13px; padding: 0; }
.field + .field { margin-top: 16px; }
.hint { color: var(--ink-3); font-size: 12.5px; margin-top: 6px; }
.input, .select {
  width: 100%; height: 44px; padding: 0 14px; border: 1px solid var(--line); border-radius: 10px;
  background: var(--surface); color: var(--ink); transition: border-color .15s, box-shadow .15s;
}
.input::placeholder { color: #9aa7ba; }
.input:hover, .select:hover { border-color: #cbd6e6; }
.input:focus, .select:focus { outline: 0; border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-soft); }
.select {
  appearance: none; -webkit-appearance: none; padding-right: 38px; cursor: pointer;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2374839a' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
  background-repeat: no-repeat; background-position: right 12px center;
}
.input-icon { position: relative; }
.input-icon > .icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--ink-3); pointer-events: none; }
.input-icon .input { padding-left: 42px; }
.input-icon .reveal { position: absolute; right: 4px; top: 50%; transform: translateY(-50%); }
.input-icon.has-reveal .input { padding-right: 46px; }
.reveal .eye-off { display: none; }
.reveal.is-on .eye-on { display: none; }
.reveal.is-on .eye-off { display: inline-block; }

fieldset { border: 0; margin: 0; padding: 0; min-width: 0; }

/* ---------- Dropzone ---------- */
.upload-grid { display: grid; grid-template-columns: minmax(0, 1.5fr) minmax(280px, 1fr); gap: 28px; align-items: stretch; }
.dropzone {
  position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px;
  min-height: 280px; padding: 32px 24px; text-align: center; cursor: pointer;
  border: 2px dashed #cdd2e2; border-radius: var(--r-lg);
  background: linear-gradient(180deg, #fcfcfe 0%, #f4f5fa 100%);
  transition: border-color .15s, background .15s, box-shadow .15s;
}
.dropzone:hover { border-color: var(--primary); background: #fff8f2; }
.dropzone:focus-within { border-color: var(--primary); box-shadow: 0 0 0 4px var(--primary-soft); }
.dropzone.is-drag { border-color: var(--primary); border-style: solid; background: var(--primary-soft); }
.dz-icon { display: grid; place-items: center; width: 64px; height: 64px; margin-bottom: 8px; border-radius: 18px; color: #fff; background: linear-gradient(135deg, var(--navy-2), #7083bd); box-shadow: 0 14px 28px -14px rgba(43, 58, 107, .55); transition: transform .2s; }
.dropzone:hover .dz-icon, .dropzone.is-drag .dz-icon { transform: translateY(-3px); }
.dz-title { font-size: 17px; font-weight: 750; color: var(--ink); max-width: 100%; overflow-wrap: anywhere; }
.dz-hint { color: var(--ink-3); font-size: 13px; }
.dz-btn { margin-top: 14px; }
.dropzone.has-file { border-style: solid; border-color: #9fd9c8; background: linear-gradient(180deg, #fbfffd, #effaf6); }
.dropzone.has-file .dz-icon { background: linear-gradient(135deg, var(--green), var(--teal)); }
.dropzone.has-error { border-color: #efb0bf; background: #fff8fa; }
.dropzone.has-error .dz-icon { background: linear-gradient(135deg, var(--rose), #e0617c); }
.dropzone.has-error .dz-title { color: var(--rose); }
.upload-side { display: flex; flex-direction: column; gap: 22px; }
.detect { display: flex; gap: 14px; align-items: flex-start; padding: 16px; border: 1px solid #cfeae3; border-radius: var(--r-md); background: linear-gradient(180deg, #f6fcfa, #ecf8f4); }
.detect strong { display: block; color: var(--ink); font-weight: 700; }
.detect p { margin-top: 4px; font-size: 13px; }
.detect-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
.steps { display: grid; gap: 14px; counter-reset: step; }
.steps li { display: flex; gap: 12px; align-items: flex-start; color: var(--ink-2); }
.steps li::before { counter-increment: step; content: counter(step); flex: none; display: grid; place-items: center; width: 24px; height: 24px; border-radius: 50%; background: var(--primary-soft); color: var(--primary); font-size: 12px; font-weight: 800; margin-top: 1px; }
.steps strong { color: var(--ink); font-weight: 650; }
.upload-side .btn { margin-top: auto; }

/* ---------- Filter ---------- */
.filters { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; padding: 12px; background: var(--surface); border: 1px solid var(--line); border-radius: var(--r-lg); box-shadow: var(--shadow-sm); }
.filters .input-icon { flex: 1 1 260px; }
.filters .select { width: auto; min-width: 170px; }
.count-pill { display: inline-flex; align-items: center; height: 24px; padding: 0 10px; margin-left: 8px; border-radius: 999px; background: var(--navy-soft); color: var(--navy-2); font-size: 12px; font-weight: 700; vertical-align: middle; }

/* ---------- Tabel ---------- */
.table-card { background: var(--surface); border: 1px solid var(--line); border-radius: var(--r-lg); box-shadow: var(--shadow-sm); overflow: hidden; }
.table-scroll { overflow-x: auto; overscroll-behavior-x: contain; }
.table-scroll:focus-visible { outline: 2px solid var(--primary); outline-offset: -2px; }
.data-table { width: 100%; border-collapse: collapse; font-size: 13.5px; text-align: left; }
.data-table th { position: sticky; top: 0; padding: 13px 12px; background: #f5f6fa; color: var(--ink-3); font-size: 12.5px; font-weight: 700; line-height: 1.35; vertical-align: bottom; border-bottom: 1px solid var(--line); }
.data-table td { padding: 14px 12px; color: var(--ink-2); border-bottom: 1px solid var(--line-soft); vertical-align: top; }
.data-table tbody:last-of-type tr:last-child td, .data-table tbody:last-child tr:last-child td { border-bottom: 0; }
.data-table tbody.group:hover td { background: #fbf9f7; }
.data-table tbody.group tr:not(:last-child) td.item { border-bottom: 1px dashed var(--line); }
.data-table .ta-r { text-align: right; }
.data-table td.item, .data-table td.num { font-variant-numeric: tabular-nums; }
.data-table .col-no { width: 52px; color: var(--ink-3); }
.data-table .sto-no { font-weight: 750; color: var(--ink); white-space: nowrap; }
.data-table a.sto-no:hover { color: var(--primary-d); }
.data-table .place { min-width: 130px; max-width: 210px; white-space: pre-line; overflow-wrap: anywhere; }
.data-table .name { min-width: 140px; color: var(--ink); font-weight: 550; }
.data-table .total { font-weight: 750; color: var(--ink); white-space: nowrap; font-variant-numeric: tabular-nums; }
.data-table .muted { color: var(--ink-3); }
.row-actions { display: flex; flex-direction: column; align-items: stretch; gap: 6px; }
.data-table tfoot th, .data-table tfoot td { position: static; background: #f7f9fc; color: var(--ink); font-size: 14px; border-top: 1px solid var(--line); border-bottom: 0; padding: 14px; }
.data-table tr.is-dup td { background: #fffaf0; }
.data-table.compact td, .data-table.compact th { padding: 11px 12px; }
.line-items { min-width: 230px; }
.line-items .li + .li { margin-top: 8px; padding-top: 8px; border-top: 1px dashed var(--line); }
.line-items .li strong { display: block; color: var(--ink); font-weight: 650; }
.line-items .li span { font-size: 12.5px; color: var(--ink-3); font-variant-numeric: tabular-nums; }
.route-cell { min-width: 220px; overflow-wrap: anywhere; }
.addr { display: block; white-space: pre-line; }
.route-cell .to { margin-top: 8px; padding-top: 8px; border-top: 1px dashed var(--line); }
.route-cell small { display: block; color: var(--ink-3); font-size: 11.5px; font-weight: 600; margin-bottom: 1px; }
.notes { margin-top: 8px; font-size: 12.5px; color: var(--amber); }
.mobile-only { display: none; }

.empty { display: flex; flex-direction: column; align-items: center; gap: 6px; padding: 56px 24px; text-align: center; }
.empty .chip-icon { width: 56px; height: 56px; border-radius: 16px; margin-bottom: 8px; background: var(--slate-soft); color: var(--ink-3); }
.empty h3 { font-size: 16px; color: var(--ink); }
.empty p { max-width: 42ch; }

/* ---------- Kartu STO (mobile) ---------- */
.sto-cards { display: none; gap: 14px; }
.sto-card { background: var(--surface); border: 1px solid var(--line); border-radius: var(--r-lg); padding: 16px; box-shadow: var(--shadow-sm); }
.sto-card .top { display: flex; justify-content: space-between; align-items: baseline; gap: 10px; }
.sto-card .sto-no { font-size: 16px; font-weight: 800; color: var(--ink); overflow-wrap: anywhere; }
.sto-card .date { color: var(--ink-3); font-size: 12.5px; white-space: nowrap; }
.sto-card .badges { display: flex; flex-wrap: wrap; gap: 6px; margin: 10px 0 12px; }
.sto-card .route { display: grid; gap: 10px; padding: 12px; border-radius: var(--r-md); background: #f7f9fc; overflow-wrap: anywhere; }
.sto-card .route small { display: block; color: var(--ink-3); font-weight: 600; font-size: 12px; }
.sto-card .route p { color: var(--ink); white-space: pre-line; }
.sto-card .items { margin-top: 12px; }
.sto-card .items li { display: flex; justify-content: space-between; gap: 12px; padding: 8px 0; border-bottom: 1px dashed var(--line); }
.sto-card .items li:last-child { border-bottom: 0; }
.sto-card .items b { color: var(--ink); font-weight: 600; }
.sto-card .items span { color: var(--ink-3); text-align: right; font-size: 12.5px; font-variant-numeric: tabular-nums; }
.sto-card .sum { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-top: 1px solid var(--line); margin-top: 4px; }
.sto-card .sum strong { font-size: 16px; color: var(--ink); font-variant-numeric: tabular-nums; }
.sto-card .actions { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }

/* ---------- Badge ---------- */
.badge { display: inline-flex; align-items: center; gap: 6px; height: 24px; padding: 0 10px; border-radius: 999px; font-size: 12px; font-weight: 700; white-space: nowrap; background: var(--slate-soft); color: var(--ink-2); }
.badge::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
.badge-green { background: var(--green-soft); color: var(--green); }
.badge-amber { background: var(--amber-soft); color: var(--amber); }
.badge-blue { background: var(--navy-soft); color: var(--navy-2); }
.badge-teal { background: var(--teal-soft); color: #0b7d6c; }
.badge-rose { background: var(--rose-soft); color: var(--rose); }
.badge-gray { background: var(--slate-soft); color: var(--ink-2); }

/* ---------- Alert & toast ---------- */
.alert { display: flex; align-items: flex-start; gap: 12px; padding: 14px 16px; border-radius: var(--r-md); border: 1px solid transparent; }
.alert > .icon { margin-top: 2px; }
.alert p, .alert ul { color: inherit; }
.alert ul { margin-top: 4px; padding-left: 18px; list-style: disc; }
.alert-info { background: var(--navy-soft); border-color: #d3d9ec; color: #25366f; }
.alert-danger { background: #fdeff2; border-color: #f4cfd8; color: #8f2a40; }
.alert-warn { background: var(--amber-soft); border-color: #f3e2b6; color: #7d560e; }
.alert-success { background: var(--green-soft); border-color: #bfe8d3; color: #0b5e41; }
.toast-stack { position: fixed; top: calc(var(--topbar-h) + 14px); right: 20px; z-index: 60; display: grid; gap: 10px; width: min(380px, calc(100vw - 32px)); }
.toast { display: flex; align-items: center; gap: 12px; padding: 12px 8px 12px 16px; background: rgba(43, 58, 107, .96); border-left: 4px solid var(--accent-line); color: #fff; border-radius: var(--r-md); box-shadow: var(--shadow-md); animation: toast-in .25s ease-out; }
.toast p { flex: 1; color: #fff; }
.toast > .icon { color: #5fe0b4; }
.toast .icon-btn { color: #aebbd0; }
.toast .icon-btn:hover { background: rgba(255, 255, 255, .1); color: #fff; }
.toast.is-leaving { opacity: 0; transform: translateY(-6px); transition: opacity .2s, transform .2s; }
@keyframes toast-in { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: none; } }

/* ---------- Pagination ---------- */
.pager { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-top: 16px; }
.pager-info { color: var(--ink-3); font-size: 13px; }
.pager-links { display: flex; align-items: center; gap: 4px; flex-wrap: wrap; }
.page-link { display: inline-grid; place-items: center; min-width: 38px; height: 38px; padding: 0 10px; border: 1px solid var(--line); border-radius: 10px; background: var(--surface); color: var(--ink-2); font-weight: 650; font-size: 13px; }
.page-link:hover { border-color: #f0c3a2; color: var(--primary-d); }
.page-link.is-current { background: var(--primary); border-color: var(--primary); color: #fff; }
.page-link.is-disabled { opacity: .45; pointer-events: none; }
.page-link.is-wide { display: inline-flex; gap: 6px; padding: 0 12px; }

/* ---------- Modal (dialog) ---------- */
.modal {
  display: none; flex-direction: column; padding: 0; border: 0; border-top: 3px solid var(--accent-line); color: var(--ink);
  width: min(1180px, calc(100vw - 32px)); max-width: none; max-height: calc(100dvh - 32px); margin: auto;
  background: var(--surface); border-radius: var(--r-xl); box-shadow: var(--shadow-lg); overflow: hidden;
}
.modal[open] { display: flex; animation: modal-in .22s cubic-bezier(.2, .8, .2, 1); }
.modal::backdrop { background: rgba(43, 58, 107, .38); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); animation: fade-in .2s ease-out; }
.modal:focus { outline: 0; }
.modal-sm { width: min(460px, calc(100vw - 32px)); }
.modal-head { display: flex; align-items: center; gap: 14px; padding: 20px 24px; border-bottom: 1px solid var(--line); }
.modal-head .titles { flex: 1; min-width: 0; }
.modal-head h2 { font-size: 19px; }
.modal-head p { font-size: 13px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.modal-body { flex: 1; min-height: 0; overflow: auto; padding: 22px 24px; overscroll-behavior: contain; }
.modal-body > * + * { margin-top: 18px; }
.modal-foot { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; padding: 16px 24px; border-top: 1px solid var(--line); background: #f9fbfd; }
.modal-foot .note { font-size: 12.5px; color: var(--ink-3); max-width: 46ch; }
.modal-foot .foot-actions { display: flex; gap: 10px; margin-left: auto; }
.modal-foot form { margin: 0; }
.summary { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
.summary > div { display: flex; align-items: center; gap: 12px; padding: 14px 16px; border: 1px solid var(--line); border-radius: var(--r-md); background: var(--surface); }
.summary strong { display: block; font-size: 22px; line-height: 1.1; color: var(--ink); font-variant-numeric: tabular-nums; }
.summary span { font-size: 12.5px; color: var(--ink-3); }
.modal-body .table-card { box-shadow: none; }
@keyframes modal-in { from { opacity: 0; transform: translateY(14px) scale(.985); } to { opacity: 1; transform: none; } }
@keyframes fade-in { from { opacity: 0; } to { opacity: 1; } }

/* ---------- Dashboard ---------- */
.stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; }
.stat { display: flex; flex-direction: column; gap: 14px; }
.stat .top { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.stat .label { color: var(--ink-2); font-weight: 600; }
.stat strong { font-size: 30px; line-height: 1.1; font-weight: 800; letter-spacing: -.02em; color: var(--ink); font-variant-numeric: tabular-nums; }
.stat strong.money { font-size: 24px; }
.stat strong small { font-size: 14px; font-weight: 600; color: var(--ink-3); letter-spacing: 0; margin-left: 4px; }
.stat .sub { font-size: 12.5px; color: var(--ink-3); margin-top: -8px; }
.grid-3 { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
.grid-2 { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
.grid-main { display: grid; grid-template-columns: minmax(0, 1.7fr) minmax(300px, 1fr); gap: 20px; align-items: start; }
.donut-wrap { display: flex; align-items: center; gap: 22px; flex-wrap: wrap; }
.donut { --percent: 0%; --color: var(--primary); --rest: var(--slate-soft); flex: none; width: 128px; height: 128px; border-radius: 50%; display: grid; place-items: center; background: conic-gradient(var(--color) 0 var(--percent), var(--rest) var(--percent) 100%); }
.donut-core { display: flex; flex-direction: column; align-items: center; justify-content: center; width: 90px; height: 90px; border-radius: 50%; background: var(--surface); line-height: 1.2; }
.donut-core strong { font-size: 22px; font-weight: 800; color: var(--ink); }
.donut-core small { font-size: 11.5px; color: var(--ink-3); }
.legend { display: grid; gap: 10px; flex: 1; min-width: 130px; }
.legend li { display: flex; align-items: center; gap: 8px; color: var(--ink-2); }
.legend i { width: 10px; height: 10px; border-radius: 3px; flex: none; }
.legend span { flex: 1; }
.legend b { color: var(--ink); font-variant-numeric: tabular-nums; }
.bars { display: flex; align-items: stretch; gap: 10px; height: 230px; }
.bar-col { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 6px; min-width: 0; }
.bar-val { font-size: 12.5px; font-weight: 700; color: var(--ink-2); font-variant-numeric: tabular-nums; }
.bar-track { flex: 1; width: 100%; display: flex; align-items: flex-end; justify-content: center; padding: 0 4px; border-radius: 10px; background: #f4f5f9; }
.bar { width: 100%; max-width: 44px; border-radius: 8px 8px 4px 4px; background: linear-gradient(180deg, #7b8dc4, #b7c2e2); }
.bar-col.is-today .bar { background: linear-gradient(180deg, #eb9c69, #f6c8a6); }
.bar-col.is-zero .bar { background: #dde5f0; }
.bar-lbl { font-size: 11.5px; color: var(--ink-3); white-space: nowrap; }
.recent-list { margin: -6px -10px 0; }
.recent { display: flex; align-items: center; gap: 12px; padding: 10px; border-radius: var(--r-md); color: inherit; transition: background .15s; }
.recent:hover { background: #faf6f3; color: inherit; }
.recent .chip-icon { width: 36px; height: 36px; border-radius: 10px; }
.recent .info { flex: 1; min-width: 0; }
.recent strong { display: block; color: var(--ink); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.recent small { color: var(--ink-3); font-size: 12.5px; }
.recent .go { color: var(--ink-3); }
.menu-card { display: flex; flex-direction: column; gap: 14px; color: inherit; transition: border-color .15s, box-shadow .15s; }
.menu-card:hover { color: inherit; border-color: #bfd0ee; box-shadow: var(--shadow-md); }
.menu-card h3 { font-size: 16px; }
.menu-card p { font-size: 13px; margin-top: 2px; }
.menu-card .go { margin-top: auto; display: inline-flex; align-items: center; gap: 6px; color: var(--primary-d); font-weight: 650; font-size: 13px; }

/* ---------- Detail STO (modal) ---------- */
.dl { display: grid; gap: 0; }
.dl > div { display: flex; justify-content: space-between; gap: 16px; padding: 11px 0; border-bottom: 1px solid var(--line-soft); }
.dl > div:last-child { border-bottom: 0; padding-bottom: 0; }
.dl dt { color: var(--ink-3); }
.dl dd { color: var(--ink); font-weight: 600; text-align: right; overflow-wrap: anywhere; }
.route-line { position: relative; display: grid; gap: 18px; padding-left: 26px; }
.route-line::before { content: ""; position: absolute; left: 7px; top: 10px; bottom: 10px; border-left: 2px dashed #c9cfe2; }
.route-line > div { position: relative; overflow-wrap: anywhere; }
.route-line > div::before { content: ""; position: absolute; left: -26px; top: 4px; width: 16px; height: 16px; border-radius: 50%; background: var(--surface); border: 4px solid var(--navy-2); }
.route-line > div:last-child::before { border-color: var(--primary); }
.route-line small { display: block; color: var(--ink-3); font-weight: 600; font-size: 12px; }
.route-line p { color: var(--ink); font-weight: 550; white-space: pre-line; }

.head-actions { display: flex; flex-wrap: wrap; gap: 10px; }
.link-btn { padding: 0; border: 0; background: none; font: inherit; text-align: left; cursor: pointer; }
.data-table .sto-no.link-btn:hover, .sto-card .sto-no.link-btn:hover { color: var(--primary-d); text-decoration: underline; }
.clamp { display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 3; line-clamp: 3; overflow: hidden; white-space: pre-line; }
.data-table .refs { min-width: 130px; }
.refs .ref { display: flex; align-items: center; gap: 8px; }
.refs .ref + .ref { margin-top: 8px; }
.refs small { width: 30px; flex: none; color: var(--ink-3); font-weight: 700; font-size: 12px; }
.refs .ref > span:not(.badge) { color: var(--ink); font-weight: 600; overflow-wrap: anywhere; }

.modal-lg { width: min(1040px, calc(100vw - 32px)); }
.modal-head h2 { display: flex; align-items: center; flex-wrap: wrap; gap: 10px; }
.modal-head h2 .badge { font-size: 11.5px; }
.modal:not(.is-editing) [data-edit] { display: none !important; }
.modal.is-editing [data-view] { display: none !important; }
.modal-foot .foot-main { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; width: 100%; }
.modal-foot .foot-confirm { display: none; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; width: 100%; }
.modal-foot .foot-confirm p { color: var(--rose); max-width: 60ch; }
.modal-foot .foot-confirm form { margin: 0; }
.modal.is-confirming .foot-main { display: none; }
.modal.is-confirming .foot-confirm { display: flex; }
.view-stack > * + * { margin-top: 18px; }
.panel { padding: 18px; border: 1px solid var(--line); border-radius: var(--r-md); background: var(--surface); }
.panel-warn { border-color: #f1d28b; background: #fffbf1; }
.panel > h3 { margin-bottom: 12px; }
.panel .alert { margin-bottom: 14px; }
.detail-cols { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
.section-title { display: flex; align-items: center; gap: 8px; margin-bottom: 12px; }
.dl { display: grid; gap: 0; }
.dl > div { display: flex; justify-content: space-between; gap: 16px; padding: 10px 0; border-bottom: 1px solid var(--line-soft); }
.dl > div:first-child { padding-top: 0; }
.dl > div:last-child { border-bottom: 0; padding-bottom: 0; }
.dl dt { color: var(--ink-3); }
.dl dd { color: var(--ink); font-weight: 600; text-align: right; overflow-wrap: anywhere; }
.route-line { position: relative; display: grid; gap: 18px; padding-left: 26px; }
.route-line::before { content: ""; position: absolute; left: 7px; top: 10px; bottom: 10px; border-left: 2px dashed #c9cfe2; }
.route-line > div { position: relative; overflow-wrap: anywhere; }
.route-line > div::before { content: ""; position: absolute; left: -26px; top: 4px; width: 16px; height: 16px; border-radius: 50%; background: var(--surface); border: 4px solid var(--navy-2); }
.route-line > div:last-child::before { border-color: var(--primary); }
.route-line small { display: block; color: var(--ink-3); font-weight: 600; font-size: 12px; }
.route-line p { color: var(--ink); font-weight: 550; white-space: pre-line; }

/* ---------- Form STO ---------- */
.form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
.form-grid .field + .field { margin-top: 0; }
.form-actions { margin-top: 16px; display: flex; justify-content: flex-end; }
.optional { color: var(--ink-3); font-weight: 500; }
.textarea { width: 100%; font: inherit; min-height: 84px; padding: 11px 14px; border: 1px solid var(--line); border-radius: 10px; background: var(--surface); color: var(--ink); resize: vertical; line-height: 1.5; transition: border-color .15s, box-shadow .15s; }
.textarea:hover { border-color: #cbd6e6; }
.textarea:focus { outline: 0; border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-soft); }
.input.is-invalid, .select.is-invalid, .textarea.is-invalid { border-color: #e48aa0; background: #fffafb; }
.field-error { margin-top: 5px; font-size: 12.5px; color: var(--rose); }
.alert + .form-grid, form > .alert { margin-bottom: 16px; }
.items-block { margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--line); }
.items-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
.item-list { display: grid; gap: 10px; }
.item-row { display: grid; grid-template-columns: minmax(0, 2.6fr) minmax(0, 1fr) minmax(0, .9fr) minmax(0, 1.2fr) minmax(0, 1.2fr) 38px; gap: 10px; align-items: start; padding: 12px; border: 1px solid var(--line); border-radius: var(--r-md); background: #fafbfd; }
.item-row .field + .field { margin-top: 0; }
.item-row .item-remove { margin-top: 30px; color: var(--ink-3); }
.item-row .item-remove:hover { background: var(--rose-soft); color: var(--rose); }
.item-row .amount { display: flex; align-items: center; height: 44px; font-weight: 700; color: var(--ink); font-variant-numeric: tabular-nums; overflow: hidden; }
.items-total { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-top: 14px; padding: 14px 16px; border-radius: var(--r-md); background: var(--navy-soft); color: var(--ink-2); font-weight: 600; }
.items-total strong { font-size: 18px; color: var(--ink); font-variant-numeric: tabular-nums; }

/* ---------- Akun ---------- */
.account-grid { display: grid; grid-template-columns: minmax(260px, 1fr) minmax(0, 1.6fr); gap: 20px; align-items: start; }
.profile { text-align: center; }
.profile .avatar { margin: 0 auto 14px; }
.profile h2 { font-size: 19px; }
.profile p { margin-top: 2px; }
.pw-rules { display: flex; gap: 10px; align-items: center; margin-top: 16px; padding: 12px 14px; border-radius: var(--r-md); background: #f7f9fc; font-size: 13px; color: var(--ink-2); }
.form-actions { margin-top: 22px; display: flex; justify-content: flex-end; }

/* ---------- Login ---------- */
.auth { min-height: 100vh; min-height: 100dvh; display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.05fr); background: var(--surface); }
.auth-tag { display: inline-flex; align-items: center; gap: 8px; margin-bottom: 18px; padding: 6px 14px; border: 1px solid rgba(242, 193, 78, .55); border-radius: 999px; background: rgba(242, 193, 78, .12); color: #f6cf6a; font-size: 13px; font-weight: 700; align-self: flex-start; }
.auth-main { display: flex; flex-direction: column; padding: 32px clamp(24px, 6vw, 72px); }
.auth-form { width: 100%; max-width: 400px; margin: auto; }
.auth-form h1 { font-size: 28px; margin-top: 36px; }
.auth-form > p { margin-top: 6px; }
.auth-form form { margin-top: 26px; }
.auth-form .btn { margin-top: 24px; }
.auth-form .alert { margin-top: 20px; }
.auth-copy { text-align: center; color: var(--ink-3); font-size: 12.5px; }
.auth-panel { position: relative; overflow: hidden; display: flex; flex-direction: column; justify-content: flex-end; padding: clamp(32px, 5vw, 64px); color: #fff; background: linear-gradient(150deg, #33447a 0%, #5568a6 100%); border-left: 3px solid var(--accent-line); }
.auth-panel::before { content: ""; position: absolute; inset: 0; background: radial-gradient(520px 320px at 85% 8%, rgba(255, 255, 255, .16), transparent 70%), radial-gradient(460px 320px at 0% 100%, rgba(229, 139, 82, .22), transparent 70%); }
.auth-panel > * { position: relative; }
.auth-art { position: absolute; top: 9%; left: 50%; width: min(78%, 480px); transform: translateX(-50%); }
.auth-panel h2 { font-size: clamp(28px, 3.2vw, 40px); line-height: 1.15; font-weight: 800; letter-spacing: -.02em; }
.auth-panel p { margin-top: 12px; color: rgba(255, 255, 255, .82); max-width: 40ch; font-size: 15px; }

/* ---------- Halaman error ---------- */
.error-wrap { flex: 1; display: grid; place-items: center; padding: 32px 20px; }
.error-card { width: min(480px, 100%); text-align: center; padding: 40px 32px; border-top: 3px solid var(--accent-line); }
.error-card .brand { margin-bottom: 28px; }
.error-code { font-size: 72px; line-height: 1; font-weight: 800; letter-spacing: -.04em; background: linear-gradient(135deg, var(--navy-2), #8395cc); -webkit-background-clip: text; background-clip: text; color: transparent; }
.error-card h2 { margin-top: 14px; font-size: 20px; }
.error-card p { margin: 8px 0 26px; }

/* ---------- Responsif ---------- */
@media (max-width: 1100px) {
  .stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .grid-3 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .grid-main, .account-grid { grid-template-columns: minmax(0, 1fr); }
}
@media (max-width: 900px) {
  .detail-cols, .form-grid { grid-template-columns: minmax(0, 1fr); }
  .form-grid > .field:empty { display: none; }
  .item-row { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .item-row .item-name { grid-column: 1 / -1; }
  .item-row .item-remove { grid-column: 1 / -1; justify-self: end; margin-top: 0; }
  .upload-grid { grid-template-columns: minmax(0, 1fr); }
  .dropzone { min-height: 220px; }
  .auth { grid-template-columns: minmax(0, 1fr); }
  .auth-panel { display: none; }
  .grid-2 { grid-template-columns: minmax(0, 1fr); }
}
@media (max-width: 820px) {
  .desk-only { display: none; }
  .sto-cards { display: grid; }
  .table-card.desk-only { display: none; }
}
@media (max-width: 720px) {
  .container { padding-inline: 16px; }
  .page { padding-top: 22px; }
  h1 { font-size: 24px; }
  .topbar-in { gap: 12px; }
  .brand .brand-name { display: none; }
  .mainnav { gap: 2px; }
  .navlink { padding: 0 12px; }
  .usermenu .uname, .usermenu .chev { display: none; }
  .usermenu summary { padding: 4px; }
  .card { padding: 18px; border-radius: 16px; }
  .stats, .grid-3 { grid-template-columns: minmax(0, 1fr); }
  .filters .select, .filters .btn, .filters .input-icon { flex: 1 1 100%; width: 100%; }
  .section-head .btn, .page-head .btn { width: 100%; }
  .page-head { padding: 20px; border-radius: 16px; }
  .head-actions { width: 100%; }
  .head-actions .btn { flex: 1 1 0; }
  .input, .select { font-size: 16px; height: 48px; }
  .pager { justify-content: center; }
  .pager-info { width: 100%; text-align: center; }
  .summary { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
  .summary > div { flex-direction: column; align-items: flex-start; gap: 2px; padding: 10px 12px; }
  .summary .chip-icon { display: none; }
  .summary strong { font-size: 20px; }
  .summary span { font-size: 11.5px; line-height: 1.3; }

  /* modal menjadi layar penuh */
  .modal, .modal-sm { width: 100vw; max-height: none; height: 100dvh; border-radius: 0; }
  .modal-sm { height: auto; width: calc(100vw - 24px); border-radius: var(--r-xl); max-height: calc(100dvh - 24px); }
  .modal-head, .modal-body, .modal-foot { padding-inline: 16px; }
  .modal-foot { position: sticky; bottom: 0; }
  .modal-foot .note { display: none; }
  .modal-foot .foot-actions { width: 100%; flex-direction: column-reverse; }
  .modal-foot .foot-actions form, .modal-foot .foot-actions .btn { width: 100%; }
  .modal-foot form .btn { width: 100%; }

  /* tabel menjadi kartu berlabel */
  .stack-table, .stack-table tbody { display: block; }
  .stack-table thead { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0, 0, 0, 0); }
  .stack-table tr { display: block; padding: 6px 14px; margin-bottom: 12px; background: var(--surface); border: 1px solid var(--line); border-radius: var(--r-md); }
  .stack-table tr.is-dup { background: #fffaf0; }
  .stack-table td { display: flex; justify-content: space-between; gap: 16px; padding: 10px 0; text-align: right; border-bottom: 1px solid var(--line-soft) !important; }
  .stack-table td:last-child { border-bottom: 0 !important; }
  .stack-table td::before { content: attr(data-label); flex: none; color: var(--ink-3); font-weight: 600; text-align: left; }
  .stack-table td > * { text-align: right; }
  .stack-table .route-cell, .stack-table .place, .stack-table .line-items { min-width: 0; }
  .stack-table .col-no { width: auto; }
  .stack-table .line-items { text-align: right; }
  .stack-table tfoot, .stack-table tfoot tr { display: block; }
  .stack-table tfoot tr { background: #f7f9fc; }
  .stack-table tfoot th, .stack-table tfoot td { display: flex; justify-content: space-between; border: 0 !important; padding: 8px 0; }
  .stack-table tfoot td::before { content: none; }
  .table-card:has(.stack-table) { border: 0; background: transparent; box-shadow: none; overflow: visible; }

  .dl > div { flex-direction: column; gap: 2px; }
  .dl dd { text-align: left; }
  .error-code { font-size: 60px; }
}
@media (max-width: 480px) {
  .navlink { padding: 0 10px; }
  .navlink .icon { display: none; }
}
@media (max-width: 380px) {
  .container { padding-inline: 12px; }
}
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after { animation-duration: .001ms !important; animation-iteration-count: 1 !important; transition-duration: .001ms !important; }
}
@media print {
  .topbar, .footer, .filters, .dropzone, .toast-stack, .pager, .row-actions, .modal { display: none !important; }
  body { background: #fff; }
  .container { max-width: none; padding: 0; }
  .card, .table-card { box-shadow: none; }
}
```

