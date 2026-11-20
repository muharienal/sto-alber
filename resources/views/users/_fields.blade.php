@php $self = $user && $user->is(auth()->user()); @endphp
<div class="field">
    <label class="field-label" for="{{ $modal }}-name">Nama</label>
    <input class="input" id="{{ $modal }}-name" name="name" value="{{ $val($modal, 'name', $user?->name) }}" maxlength="100" required>
</div>
<div class="form-grid">
    <div class="field">
        <label class="field-label" for="{{ $modal }}-username">Username</label>
        <input class="input" id="{{ $modal }}-username" name="username" value="{{ $val($modal, 'username', $user?->username) }}" minlength="3" maxlength="50" pattern="[a-z0-9._\-]+" autocapitalize="none" autocomplete="off" spellcheck="false" required>
        <span class="hint">Huruf kecil, angka, titik, _ atau -.</span>
    </div>
    <div class="field">
        <label class="field-label" for="{{ $modal }}-role">Peran</label>
        <select class="select" id="{{ $modal }}-role" name="role" @disabled($self)>
            @foreach($roles as $key => $label)
                <option value="{{ $key }}" @selected($val($modal, 'role', $user?->role ?? 'petugas') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @if($self)<input type="hidden" name="role" value="{{ $user->role }}">@endif
    </div>
</div>
