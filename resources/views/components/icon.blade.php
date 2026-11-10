@props(['name', 'size' => 20])
<svg {{ $attributes->merge(['class' => 'icon']) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
@switch($name)
@case('package')<path d="m12 3 8 4.5v9L12 21l-8-4.5v-9z"/><path d="m4 7.5 8 4.5 8-4.5"/><path d="M12 12v9"/>@break
@case('upload')<path d="M12 15V3"/><path d="m7 8 5-5 5 5"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>@break
@case('download')<path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>@break
@case('file')<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/><path d="M9 13h6"/><path d="M9 17h6"/>@break
@case('search')<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>@break
@case('x')<path d="M18 6 6 18"/><path d="m6 6 12 12"/>@break
@case('check')<path d="m5 12 5 5L20 7"/>@break
@case('check-circle')<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>@break
@case('alert')<path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/>@break
@case('info')<circle cx="12" cy="12" r="9"/><path d="M12 11v5"/><path d="M12 8h.01"/>@break
@case('grid')<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>@break
@case('user')<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>@break
@case('logout')<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>@break
@case('eye')<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>@break
@case('eye-off')<path d="M3 3l18 18"/><path d="M10.6 5.1A10.6 10.6 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4.1"/><path d="M6.6 6.6C3.7 8.4 2 12 2 12s3.6 7 10 7a10 10 0 0 0 4.4-1"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>@break
@case('arrow-left')<path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>@break
@case('arrow-right')<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>@break
@case('chevron-down')<path d="m6 9 6 6 6-6"/>@break
@case('chevron-left')<path d="m15 18-6-6 6-6"/>@break
@case('chevron-right')<path d="m9 18 6-6-6-6"/>@break
@case('trash')<path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="m19 6-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/>@break
@case('shield')<path d="M12 3 4 6v6c0 5 3.4 8 8 9 4.6-1 8-4 8-9V6z"/><path d="m9 12 2 2 4-4"/>@break
@case('clock')<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>@break
@case('external')<path d="M14 4h6v6"/><path d="M20 4 10 14"/><path d="M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>@break
@case('rotate')<path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/>@break
@case('wallet')<path d="M20 7H5a2 2 0 0 1 0-4h13v4"/><path d="M3 5v14a2 2 0 0 0 2 2h15V7"/><path d="M16 14h2"/>@break
@case('key')<circle cx="8" cy="15" r="4"/><path d="m11 12 9-9"/><path d="m16 7 3 3"/>@break
@case('chart')<path d="M3 3v18h18"/><path d="m7 15 4-5 3 3 5-7"/>@break
@case('lock')<rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>@break
@case('layers')<path d="m12 3 9 5-9 5-9-5z"/><path d="m3 13 9 5 9-5"/>@break
@case('inbox')<path d="M3 13h5l1.5 3h5L16 13h5"/><path d="M5.5 5h13L21 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-6z"/>@break
@case('plus')<path d="M12 5v14"/><path d="M5 12h14"/>@break
@case('edit')<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>@break
@endswitch
</svg>
