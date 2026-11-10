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
