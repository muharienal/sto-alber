@extends('layouts.app')
@section('title', 'Akun')

@section('content')
<div class="page-head">
    <div>
        <h1>Akun</h1>
        <p class="lead">Profil dan password akun Anda.</p>
    </div>
</div>

<div class="account-grid">
    <section class="card profile" aria-label="Profil">
        <span class="avatar avatar-lg">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
        <h2>{{ auth()->user()->name }}</h2>
        <p>Username: {{ auth()->user()->username }}</p>
    </section>

    <section class="card" aria-labelledby="pw-title">
        <div class="card-head">
            <div class="card-title">
                <span class="chip-icon tone-blue"><x-icon name="lock" :size="20" /></span>
                <div><h2 id="pw-title">Ubah Password</h2></div>
            </div>
        </div>

        <form method="post" action="{{ route('password.update') }}" data-loading>
            @csrf @method('PUT')
            <div class="field">
                <label class="field-label" for="current_password">Password saat ini</label>
                <input class="input" type="password" id="current_password" name="current_password" autocomplete="current-password" required>
            </div>
            <div class="field">
                <label class="field-label" for="password">Password baru</label>
                <input class="input" type="password" id="password" name="password" autocomplete="new-password" minlength="12" required>
            </div>
            <div class="field">
                <label class="field-label" for="password_confirmation">Ulangi password baru</label>
                <input class="input" type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required>
            </div>
            <div class="pw-rules"><x-icon name="info" :size="18" />Minimal 12 karakter.</div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary" data-submit data-loading-text="Menyimpan…">Simpan Password</button>
            </div>
        </form>
    </section>
</div>
@endsection
