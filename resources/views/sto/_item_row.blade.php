{{-- Satu baris barang pada form STO. Dipakai untuk baris awal dan sebagai template "Tambah barang". --}}
@php
    $fieldError = fn (string $field) => $bag->first("items.$i.$field");
    $uid = $rid.'-i'.$i;
@endphp
<div class="item-row" data-item-row>
    <div class="field item-name">
        <label class="field-label" for="{{ $uid }}-name">Nama barang</label>
        <input class="input {{ $fieldError('name') ? 'is-invalid' : '' }}" id="{{ $uid }}-name" name="items[{{ $i }}][name]" value="{{ $item['name'] ?? '' }}" maxlength="255" required placeholder="Contoh: Urea Prill @50 KG">
        @if($fieldError('name'))<p class="field-error">{{ $fieldError('name') }}</p>@endif
    </div>
    <div class="field">
        <label class="field-label" for="{{ $uid }}-qty">Qty</label>
        <input class="input {{ $fieldError('quantity') ? 'is-invalid' : '' }}" id="{{ $uid }}-qty" type="number" step="any" min="0" inputmode="decimal" name="items[{{ $i }}][quantity]" value="{{ $item['quantity'] ?? '' }}" required data-q>
        @if($fieldError('quantity'))<p class="field-error">{{ $fieldError('quantity') }}</p>@endif
    </div>
    <div class="field">
        <label class="field-label" for="{{ $uid }}-unit">Satuan</label>
        <input class="input {{ $fieldError('unit') ? 'is-invalid' : '' }}" id="{{ $uid }}-unit" list="{{ $rid }}-units" name="items[{{ $i }}][unit]" value="{{ $item['unit'] ?? '' }}" maxlength="20" required>
        @if($fieldError('unit'))<p class="field-error">{{ $fieldError('unit') }}</p>@endif
    </div>
    <div class="field">
        <label class="field-label" for="{{ $uid }}-rate">Tarif</label>
        <input class="input {{ $fieldError('rate') ? 'is-invalid' : '' }}" id="{{ $uid }}-rate" type="number" step="any" min="0" inputmode="decimal" name="items[{{ $i }}][rate]" value="{{ $item['rate'] ?? '' }}" required data-r>
        @if($fieldError('rate'))<p class="field-error">{{ $fieldError('rate') }}</p>@endif
    </div>
    <div class="field item-amount">
        <span class="field-label">Biaya</span>
        <output class="amount" data-amount>0</output>
    </div>
    <button type="button" class="icon-btn item-remove" data-item-remove aria-label="Hapus baris"><x-icon name="trash" :size="18" /></button>
</div>
