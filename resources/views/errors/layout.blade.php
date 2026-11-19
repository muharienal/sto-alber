<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('message') · {{ config('app.brand') }}</title>
    @include('partials.assets')
</head>
<body>
<main class="err-wrap">
    <div>
        <section class="err-card">
            <a class="brand err-brand" href="{{ url('/') }}" aria-label="{{ config('app.brand') }}">@include('partials.lockup')</a>
            <div class="err-code">@yield('code')</div>
            <h1>@yield('message')</h1>
            <p>@yield('detail')</p>
            <div class="err-actions">
                <a class="btn btn-cta" href="{{ url('/') }}">Ke Dashboard</a>
                @php $prev = url()->previous(); @endphp
                @if($prev !== url()->current())
                    <a class="btn btn-secondary" href="{{ $prev }}"><x-icon name="arrow-left" :size="16" />Kembali</a>
                @endif
            </div>
        </section>
        <p class="err-copy">@include('partials.copyright')</p>
    </div>
</main>
</body>
</html>
