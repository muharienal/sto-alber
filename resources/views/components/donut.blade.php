@php
    $pct = $total > 0 ? round($value / $total * 100, 1) : 0;
    $ringColor = $total > 0 ? $color : '#dfe5ee';
    $ringRest = $total > 0 ? $rest : '#dfe5ee';
@endphp
<div class="donut-wrap">
    <div class="donut" style="--percent:{{ $pct }}%;--color:{{ $ringColor }};--rest:{{ $ringRest }}" role="img" aria-label="{{ $label }}: {{ $value }} dari {{ $total }} STO ({{ (int) round($pct) }} persen)">
        <div class="donut-core"><strong>{{ (int) round($pct) }}%</strong><small>{{ $label }}</small></div>
    </div>
    <ul class="legend">
        <li><i style="background:{{ $color }}"></i><span>{{ $label }}</span><b>{{ $value }}</b></li>
        <li><i style="background:{{ $rest }}"></i><span>{{ $other }}</span><b>{{ $total - $value }}</b></li>
    </ul>
</div>
