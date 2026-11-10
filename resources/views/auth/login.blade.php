<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Masuk · STO Alber</title>
    @include('partials.assets')
</head>
<body>
<div class="auth">
    <main class="auth-main">
        <a class="brand" href="{{ route('login') }}" aria-label="STO Alber">
            <x-logo :size="40" class="brand-logo" />
            <span class="brand-name">STO <b>Alber</b></span>
        </a>

        <div class="auth-form">
            <h1>Masuk</h1>
            <p>Gunakan username dan password Anda.</p>

            @if($errors->any())
                <div class="alert alert-danger" role="alert">
                    <x-icon name="alert" :size="20" />
                    <p>{{ $errors->first() }}</p>
                </div>
            @endif

            <form method="post" action="{{ route('login.submit') }}" data-loading>
                @csrf
                <div class="field">
                    <label class="field-label" for="username">Username</label>
                    <div class="input-icon">
                        <x-icon name="user" :size="18" />
                        <input class="input" id="username" name="username" value="{{ old('username') }}" autocomplete="username" required autofocus placeholder="Masukkan username">
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
                <button type="submit" class="btn btn-primary btn-lg btn-block" data-submit data-loading-text="Memproses…">Masuk</button>
            </form>
        </div>

        <p class="auth-copy">@include('partials.copyright')</p>
    </main>

    <aside class="auth-panel" aria-hidden="true">
        <svg class="auth-art" viewBox="0 0 480 330" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="8" y="296" width="464" height="10" rx="5" fill="#fff" opacity=".18"/>
            {{-- palet kiri --}}
            <rect x="40" y="276" width="170" height="18" rx="4" fill="#fff" opacity=".35"/>
            <rect x="48" y="226" width="74" height="46" rx="9" fill="#fff" opacity=".92"/>
            <rect x="128" y="226" width="74" height="46" rx="9" fill="#fff" opacity=".7"/>
            <rect x="48" y="176" width="74" height="46" rx="9" fill="#fff" opacity=".6"/>
            <rect x="128" y="176" width="74" height="46" rx="9" fill="#fff" opacity=".92"/>
            <rect x="88" y="126" width="74" height="46" rx="9" fill="#fff" opacity=".8"/>
            {{-- palet kanan --}}
            <rect x="260" y="276" width="190" height="18" rx="4" fill="#fff" opacity=".35"/>
            <rect x="268" y="226" width="80" height="46" rx="9" fill="#fff" opacity=".7"/>
            <rect x="354" y="226" width="80" height="46" rx="9" fill="#fff" opacity=".92"/>
            <rect x="268" y="176" width="80" height="46" rx="9" fill="#fff" opacity=".92"/>
            <rect x="354" y="176" width="80" height="46" rx="9" fill="#fff" opacity=".6"/>
            <rect x="268" y="126" width="80" height="46" rx="9" fill="#fff" opacity=".6"/>
            <rect x="354" y="126" width="80" height="46" rx="9" fill="#fff" opacity=".85"/>
            <rect x="310" y="76" width="80" height="46" rx="9" fill="#fff" opacity=".92"/>
            {{-- tanda dokumen --}}
            <rect x="196" y="26" width="92" height="64" rx="12" fill="#e99a68"/>
            <path d="M222 48h40M222 60h40M222 72h24" stroke="#fff" stroke-width="4" stroke-linecap="round"/>
        </svg>
        <span class="auth-tag">STO Alat Berat</span>
        <h2>Data STO<br>rapi di satu tempat.</h2>
        <p>Impor dari PDF, periksa, lalu simpan.</p>
    </aside>
</div>
</body>
</html>
