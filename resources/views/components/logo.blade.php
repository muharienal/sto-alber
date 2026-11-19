{{--
    Ikon aplikasi STO Alat Berat Wilayah 1: lembar dokumen STO dengan lencana centang (dokumen lengkap).
    Berkas statis yang sama: public/images/logo-sto-alat-berat.svg dan public/favicon.svg.
    Pemakaian inline: <x-logo :size="40" />
--}}
@props(['size' => 38])
@php $gid = 'sto-ab-'.\Illuminate\Support\Str::random(6); @endphp
<svg {{ $attributes->merge(['class' => 'logo-mark']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 512 512" role="img" aria-hidden="true" focusable="false"><defs><linearGradient id="{{ $gid }}" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#52525B"/><stop offset=".5" stop-color="#27272A"/><stop offset="1" stop-color="#18181B"/></linearGradient></defs><rect width="512" height="512" rx="116" fill="url(#{{ $gid }})"/><rect x="118" y="118" width="212" height="276" rx="30" fill="#FFFFFF" opacity=".28" transform="rotate(-8 224 256)"/><path d="M178 96h126l78 78v206a36 36 0 0 1-36 36H178a36 36 0 0 1-36-36V132a36 36 0 0 1 36-36z" fill="#FFFFFF"/><path d="M304 96v52a26 26 0 0 0 26 26h52z" fill="#FEF08A"/><rect x="184" y="196" width="92" height="22" rx="11" fill="#EAB308"/><rect x="184" y="242" width="150" height="16" rx="8" fill="#D4D4D8"/><rect x="184" y="276" width="124" height="16" rx="8" fill="#D4D4D8"/><rect x="184" y="310" width="72" height="16" rx="8" fill="#D4D4D8"/><circle cx="350" cy="364" r="74" fill="#FACC15" stroke="#27272A" stroke-width="14"/><path d="M316 364l24 24 46-48" fill="none" stroke="#1C1917" stroke-width="22" stroke-linecap="round" stroke-linejoin="round"/></svg>
