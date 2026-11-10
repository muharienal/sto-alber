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
