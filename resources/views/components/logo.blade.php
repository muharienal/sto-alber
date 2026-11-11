{{--
    Logo STO Alber: ekskavator (alat berat) di atas kotak navy.
    Pakai: <x-logo :size="40" />  — salinan berkas yang sama ada di public/favicon.svg.
--}}
@props(['size' => 38])
@php $gid = 'sto-logo-'.\Illuminate\Support\Str::random(6); @endphp
<svg {{ $attributes->merge(['class' => 'logo-mark']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 64 64" role="img" aria-hidden="true" focusable="false">
  <defs>
    <linearGradient id="{{ $gid }}-bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="#33509b"/>
      <stop offset="1" stop-color="#17204a"/>
    </linearGradient>
    <linearGradient id="{{ $gid }}-or" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0" stop-color="#f6a54a"/>
      <stop offset="1" stop-color="#e2762b"/>
    </linearGradient>
  </defs>
  <rect width="64" height="64" rx="16" fill="url(#{{ $gid }}-bg)"/>
  <g transform="translate(2.5 -1.5)">
  <!-- lengan (boom + stick) -->
  <path d="M30 33 42 15.5 51.5 31" fill="none" stroke="url(#{{ $gid }}-or)" stroke-width="5.2" stroke-linecap="round" stroke-linejoin="round"/>
  <!-- bucket -->
  <path d="M47.5 30.5h9l-2.2 9.5c-.3 1.3-1.5 2-2.8 1.8l-5.6-1.2z" fill="#fff"/>
  <!-- bodi dan kabin -->
  <path d="M10 34.5c0-1.4 1.1-2.5 2.5-2.5H33c1.4 0 2.5 1.1 2.5 2.5V42H10z" fill="url(#{{ $gid }}-or)"/>
  <path d="M12.5 32V23.5c0-1.4 1.1-2.5 2.5-2.5h7.5c1.2 0 2.2.8 2.4 2l1.6 9z" fill="url(#{{ $gid }}-or)"/>
  <path d="M15.5 24h6.3l1.2 6h-7.5z" fill="#cfe0ff"/>
  <!-- roda rantai -->
  <rect x="7" y="44" width="32" height="10" rx="5" fill="#fff"/>
  <circle cx="12.5" cy="49" r="2.4" fill="#23336d"/>
  <circle cx="23" cy="49" r="2.4" fill="#23336d"/>
  <circle cx="33.5" cy="49" r="2.4" fill="#23336d"/>
  </g>
</svg>
