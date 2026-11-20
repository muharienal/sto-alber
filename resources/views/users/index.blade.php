@extends('layouts.app')
@section('title', 'Pengguna')

@section('content')
@php
    $bag = $errors->getBag('user');
    $openModal = $bag->any() ? old('_modal') : null;
    $roles = \App\Models\User::ROLES;
    $val = fn (string $modal, string $key, $default = null) => $openModal === $modal ? old($key, $default) : $default;
@endphp

<div class="page-head">
    <div>
        <h1>Pengguna</h1>
        <p class="lead">Admin bisa semua, termasuk menghapus STO dan mengelola pengguna. Petugas input bisa menambah, mengubah, dan melengkapi STO.</p>
    </div>
    <div class="head-actions">
        <button type="button" class="btn btn-cta" data-dialog-open="user-create"><x-icon name="plus" :size="16" />Tambah Pengguna</button>
    </div>
</div>

<section class="card list-card" aria-labelledby="users-title">
    <div class="card-head"><h2 id="users-title">Daftar Pengguna <span class="count-pill">{{ $users->count() }} akun</span></h2></div>
    <div class="table-scroll" tabindex="0" role="region" aria-label="Daftar pengguna">
        <table class="data-table stack-table users-table">
            <thead><tr><th scope="col">Nama</th><th scope="col">Peran</th><th scope="col">Status</th><th scope="col">Terakhir masuk</th><th scope="col"><span class="sr-only">Aksi</span></th></tr></thead>
            <tbody>
                @foreach($users as $user)
                    <tr class="{{ $user->is_active ? '' : 'is-muted' }}">
                        <td data-label="Nama"><strong>{{ $user->name }}</strong>@if($user->is(auth()->user())) <span class="hint">(Anda)</span>@endif<br><span class="hint">{{ $user->username }}</span></td>
                        <td data-label="Peran"><span class="badge {{ $user->isAdmin() ? 'badge-blue' : 'badge-teal' }}">{{ $user->roleLabel() }}</span></td>
                        <td data-label="Status">@if($user->is_active)<span class="status-dot is-on">Aktif</span>@else<span class="status-dot">Nonaktif</span>@endif</td>
                        <td data-label="Terakhir masuk" class="num">{{ $user->last_login_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td data-label="Aksi" class="ta-r"><button type="button" class="btn btn-secondary btn-sm" data-dialog-open="user-{{ $user->id }}"><x-icon name="edit" :size="15" />Ubah</button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

{{-- ============ Tambah pengguna ============ --}}
<dialog class="modal modal-md" id="user-create" aria-labelledby="user-create-title" @if($openModal === 'user-create') data-autoopen @endif>
    <div class="modal-head">
        <span class="chip-icon tone-yellow"><x-icon name="user" :size="20" /></span>
        <div class="titles"><h2 id="user-create-title">Tambah Pengguna</h2><p>Berikan username dan password awal kepada pengguna.</p></div>
        <button type="button" class="icon-btn" data-dialog-close aria-label="Tutup"><x-icon name="x" :size="20" /></button>
    </div>
    <form class="modal-body" id="form-user-create" method="post" action="{{ route('users.store') }}" data-loading>
        @csrf
        <input type="hidden" name="_modal" value="user-create">
        @if($openModal === 'user-create')@include('users._errors')@endif
        @include('users._fields', ['modal' => 'user-create', 'user' => null])
        <div class="field">
            <label class="field-label" for="user-create-password">Password awal</label>
            <input class="input" type="password" id="user-create-password" name="password" autocomplete="new-password" minlength="12" required>
        </div>
        <div class="field">
            <label class="field-label" for="user-create-password2">Ulangi password</label>
            <input class="input" type="password" id="user-create-password2" name="password_confirmation" autocomplete="new-password" required>
        </div>
        <p class="hint">Minimal 12 karakter. Pengguna bisa menggantinya sendiri di menu Akun &amp; Password.</p>
    </form>
    <div class="modal-foot">
        <span></span>
        <div class="foot-actions">
            <button type="button" class="btn btn-secondary" data-dialog-close>Batal</button>
            <button type="submit" form="form-user-create" class="btn btn-primary" data-submit data-loading-text="Menyimpan…"><x-icon name="check" :size="18" /><span>Simpan</span></button>
        </div>
    </div>
</dialog>

{{-- ============ Ubah pengguna & reset password ============ --}}
@foreach($users as $user)
    @php $modal = 'user-'.$user->id; $self = $user->is(auth()->user()); @endphp
    <dialog class="modal modal-md" id="{{ $modal }}" aria-labelledby="{{ $modal }}-title" @if($openModal === $modal || $openModal === $modal.'-pw') data-autoopen @endif>
        <div class="modal-head">
            <span class="chip-icon tone-yellow"><x-icon name="user" :size="20" /></span>
            <div class="titles"><h2 id="{{ $modal }}-title">{{ $user->name }}</h2><p>{{ $user->username }}</p></div>
            <button type="button" class="icon-btn" data-dialog-close aria-label="Tutup"><x-icon name="x" :size="20" /></button>
        </div>
        <div class="modal-body">
            <form id="form-{{ $modal }}" method="post" action="{{ route('users.update', $user) }}" data-loading>
                @csrf @method('PUT')
                <input type="hidden" name="_modal" value="{{ $modal }}">
                @if($openModal === $modal)@include('users._errors')@endif
                @include('users._fields', ['modal' => $modal, 'user' => $user])
                <div class="field">
                    <label class="field-label" for="{{ $modal }}-active">Status</label>
                    <select class="select" id="{{ $modal }}-active" name="is_active" @disabled($self)>
                        <option value="1" @selected((string) $val($modal, 'is_active', (int) $user->is_active) === '1')>Aktif, bisa masuk</option>
                        <option value="0" @selected((string) $val($modal, 'is_active', (int) $user->is_active) === '0')>Nonaktif, tidak bisa masuk</option>
                    </select>
                    @if($self)<input type="hidden" name="is_active" value="1"><span class="hint">Akun Anda sendiri tidak bisa dinonaktifkan.</span>@endif
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" data-submit data-loading-text="Menyimpan…"><x-icon name="check" :size="18" /><span>Simpan</span></button>
                </div>
            </form>

            <form class="reset-pw" method="post" action="{{ route('users.password', $user) }}" data-loading>
                @csrf @method('PUT')
                <input type="hidden" name="_modal" value="{{ $modal }}-pw">
                <h3>Reset password</h3>
                @if($openModal === $modal.'-pw')@include('users._errors')@endif
                <div class="form-grid">
                    <div class="field">
                        <label class="field-label" for="{{ $modal }}-pw">Password baru</label>
                        <input class="input" type="password" id="{{ $modal }}-pw" name="password" autocomplete="new-password" minlength="12" required>
                    </div>
                    <div class="field">
                        <label class="field-label" for="{{ $modal }}-pw2">Ulangi</label>
                        <input class="input" type="password" id="{{ $modal }}-pw2" name="password_confirmation" autocomplete="new-password" required>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-secondary" data-submit data-loading-text="Menyimpan…"><x-icon name="key" :size="16" /><span>Reset Password</span></button>
                </div>
            </form>
        </div>
    </dialog>
@endforeach
@endsection
