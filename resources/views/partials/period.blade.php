{{-- Pilihan periode (Dashboard, Rekap). Butuh $period (App\Support\Period) dan $periodRoute (nama route). --}}
@php $activePreset = $period->presetKey(); @endphp
<form class="period-bar" method="get" action="{{ route($periodRoute) }}" aria-label="Pilih periode">
    <span class="period-chips" role="group" aria-label="Periode cepat">
        @foreach(\App\Support\Period::presets() as $key => [$label, $from, $to])
            <a class="chip {{ $activePreset === $key ? 'is-on' : '' }}" href="{{ route($periodRoute, array_filter(['dari' => $from, 'sampai' => $to])) }}" @if($activePreset === $key) aria-current="true" @endif>{{ $label }}</a>
        @endforeach
    </span>
    <label class="period">
        <span>Tanggal STO</span>
        <input class="input" type="date" name="dari" value="{{ $period->from }}" aria-label="Tanggal STO dari">
        <span class="sep">s/d</span>
        <input class="input" type="date" name="sampai" value="{{ $period->to }}" aria-label="Tanggal STO sampai">
    </label>
    <button type="submit" class="btn btn-secondary"><x-icon name="calendar" :size="16" />Terapkan</button>
</form>
