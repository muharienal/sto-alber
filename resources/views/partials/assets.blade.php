{{-- Ikon, warna tema, CSS dan JS. Huruf Inria Sans disimpan lokal (public/assets/fonts) agar tetap tampil di jaringan tanpa internet. --}}
<meta name="color-scheme" content="light">
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=7">
<link rel="icon" type="image/png" sizes="48x48" href="{{ asset('favicon-48.png') }}?v=7">
<link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=7">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}?v=7">
<meta name="theme-color" content="#1A1433">
<meta name="apple-mobile-web-app-title" content="STO Alat Berat Wilayah 1">
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) }}">
<script src="{{ asset('js/app.js') }}?v={{ @filemtime(public_path('js/app.js')) }}" defer></script>
