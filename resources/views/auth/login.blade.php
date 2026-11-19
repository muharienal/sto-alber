<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Masuk · {{ config('app.brand') }}</title>
    @include('partials.assets')
</head>
<body class="login-page">
<main class="login">
    <div class="login-card">
        {{-- ===== Form ===== --}}
        <section class="login-form">
            <div class="login-brand">
                <span class="co-tile"><img src="{{ asset('images/logo-pcs.png') }}" alt="PT Petrokopindo Cipta Selaras"></span>
                <span class="brand-sep"></span>
                <span class="brand">@include('partials.lockup')</span>
            </div>

            <div class="login-head">
                <span class="eyebrow">Master dokumen STO</span>
                <h1>Masuk ke akun Anda</h1>
                <p>Gunakan username dan password yang diberikan admin.</p>
            </div>

            <form method="post" action="{{ route('login.submit') }}" class="auth-form" data-loading>
                @csrf
                @if($errors->any())
                    <div class="alert alert-error" role="alert">
                        <x-icon name="alert" :size="18" />
                        <p>{{ $errors->first() }}</p>
                    </div>
                @endif

                <div class="field">
                    <label class="field-label" for="username">Username</label>
                    <div class="input-icon">
                        <x-icon name="user" :size="18" />
                        <input class="input {{ $errors->any() ? 'is-invalid' : '' }}" id="username" name="username" value="{{ old('username') }}" autocomplete="username" required autofocus placeholder="Masukkan username">
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
                <button type="submit" class="btn btn-cta btn-lg btn-block" data-submit data-loading-text="Memproses…">Masuk <x-icon name="arrow-right" :size="18" /></button>
            </form>

            <p class="login-copy">@include('partials.copyright')</p>
        </section>

        {{-- ===== Panel judul ===== --}}
        <aside class="login-visual">
            <div class="login-caption">
                <span class="eyebrow">STO Alat Berat Wilayah 1</span>
                <h2>Dokumen STO, <em>rapi dan lengkap</em> di satu tempat.</h2>
                <p>Impor STO langsung dari PDF, periksa pratinjaunya, lalu lengkapi SOJ dan MMJ sampai tuntas.</p>
            </div>
        </aside>
    </div>
</main>
</body>
</html>
