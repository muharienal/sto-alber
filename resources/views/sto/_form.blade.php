{{--
    Form tambah / ubah STO (hanya isi form; tombol simpan ada pada footer modal lewat atribut form="").
    Parameter: $formId, $action, $method (POST|PUT), $sto (Sto|null), $modalId, $formName, $submitted (bool)
--}}
@php
    $bag = $submitted ? $errors->getBag('sto') : new \Illuminate\Support\MessageBag();
    $val = fn (string $key, $default = '') => $submitted ? old($key, '') : $default;
    $trim = fn ($n, int $d) => rtrim(rtrim(number_format((float) $n, $d, '.', ''), '0'), '.');
    if ($submitted) {
        $items = array_values(array_filter((array) old('items', []), 'is_array'));
    } elseif ($sto) {
        $items = $sto->items->map(fn ($it) => [
            'name' => $it->name,
            'quantity' => $trim($it->quantity, 3),
            'unit' => $it->unit,
            'rate' => $trim($it->rate, 2),
        ])->all();
    } else {
        $items = [];
    }
    if (count($items) === 0) {
        $items = [['name' => '', 'quantity' => '', 'unit' => 'TON', 'rate' => '']];
    }
@endphp
<form id="{{ $formId }}" method="post" action="{{ $action }}" data-sto-form data-loading @if($submitted && $sto) data-reload-on-cancel @endif>
    @csrf
    @if($method !== 'POST') @method($method) @endif
    <input type="hidden" name="_modal" value="{{ $modalId }}">
    <input type="hidden" name="_form" value="{{ $formName }}">

    @if($bag->any())
        <div class="alert alert-danger" role="alert">
            <x-icon name="alert" :size="20" />
            <div>
                <strong>Periksa isian berikut:</strong>
                <ul>@foreach($bag->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
            </div>
        </div>
    @endif

    <div class="form-grid">
        <div class="field">
            <label class="field-label" for="{{ $formId }}-number">Nomor STO</label>
            <input class="input {{ $bag->has('number') ? 'is-invalid' : '' }}" id="{{ $formId }}-number" name="number" value="{{ $val('number', $sto?->number ?? '') }}" maxlength="40" required autocomplete="off">
        </div>
        <div class="field">
            <label class="field-label" for="{{ $formId }}-date">Tanggal</label>
            <input class="input {{ $bag->has('document_date') ? 'is-invalid' : '' }}" id="{{ $formId }}-date" type="date" name="document_date" value="{{ $val('document_date', $sto?->document_date?->format('Y-m-d') ?? '') }}" required>
        </div>
        <div class="field">
            <label class="field-label" for="{{ $formId }}-activity">Kegiatan</label>
            @php $activity = $val('activity', $sto?->activity ?? ''); @endphp
            <select class="select {{ $bag->has('activity') ? 'is-invalid' : '' }}" id="{{ $formId }}-activity" name="activity" required>
                <option value="" @selected($activity === '')>Pilih</option>
                <option value="BONGKAR" @selected($activity === 'BONGKAR')>BONGKAR</option>
                <option value="MUAT" @selected($activity === 'MUAT')>MUAT</option>
            </select>
        </div>
        <div class="field"></div>
        <div class="field">
            <label class="field-label" for="{{ $formId }}-soj">SOJ <span class="optional">(opsional)</span></label>
            <input class="input {{ $bag->has('soj') ? 'is-invalid' : '' }}" id="{{ $formId }}-soj" name="soj" value="{{ $val('soj', $sto?->soj ?? '') }}" maxlength="100" autocomplete="off">
        </div>
        <div class="field">
            <label class="field-label" for="{{ $formId }}-mmj">MMJ <span class="optional">(opsional)</span></label>
            <input class="input {{ $bag->has('mmj') ? 'is-invalid' : '' }}" id="{{ $formId }}-mmj" name="mmj" value="{{ $val('mmj', $sto?->mmj ?? '') }}" maxlength="100" autocomplete="off">
        </div>
        <div class="field">
            <label class="field-label" for="{{ $formId }}-origin">Asal</label>
            <textarea class="textarea {{ $bag->has('origin') ? 'is-invalid' : '' }}" id="{{ $formId }}-origin" name="origin" rows="3" maxlength="1000" required>{{ $val('origin', $sto?->origin ?? '') }}</textarea>
        </div>
        <div class="field">
            <label class="field-label" for="{{ $formId }}-destination">Tujuan</label>
            <textarea class="textarea {{ $bag->has('destination') ? 'is-invalid' : '' }}" id="{{ $formId }}-destination" name="destination" rows="3" maxlength="1000" required>{{ $val('destination', $sto?->destination ?? '') }}</textarea>
        </div>
    </div>

    <div class="items-block">
        <div class="items-head">
            <h3>Barang</h3>
            <button type="button" class="btn btn-secondary btn-sm" data-item-add><x-icon name="plus" :size="16" />Tambah baris</button>
        </div>
        <datalist id="{{ $formId }}-units"><option value="TON"></option><option value="KG"></option><option value="ZAK"></option><option value="PCS"></option></datalist>
        <div class="item-list" data-item-list>
            @foreach($items as $i => $item)
                @include('sto._item_row', ['i' => $i, 'item' => $item, 'bag' => $bag, 'rid' => $formId])
            @endforeach
        </div>
        <template data-item-template>
            @include('sto._item_row', ['i' => '__INDEX__', 'item' => ['name' => '', 'quantity' => '', 'unit' => 'TON', 'rate' => ''], 'bag' => new \Illuminate\Support\MessageBag(), 'rid' => $formId])
        </template>
        <div class="items-total"><span>Total</span><strong>Rp <span data-total>0</span></strong></div>
    </div>
</form>
