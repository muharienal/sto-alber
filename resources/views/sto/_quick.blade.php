{{--
    Mode isi cepat: SOJ dan MMJ diketik langsung di tabel, lalu disimpan sekaligus.
    Setiap baris membawa nilai awal (soj_awal/mmj_awal) agar server hanya menyimpan yang diubah
    dan melewati baris yang sudah diubah orang lain sejak halaman dibuka.
    Parameter: $stos, $rupiah
--}}
@php $quickErrors = $errors->getBag('quick'); @endphp
<form id="quick-form" method="post" action="{{ route('sto.references.bulk') }}" data-loading data-quick-form>
    @csrf @method('PATCH')

    @if($quickErrors->any())
        <div class="alert alert-error quick-alert" role="alert">
            <x-icon name="alert" :size="18" />
            <ul>@foreach($quickErrors->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="table-scroll" tabindex="0" role="region" aria-label="Isi cepat SOJ dan MMJ">
        <table class="data-table quick-table stack-table">
            <thead>
                <tr>
                    <th scope="col">Nomor STO</th>
                    <th scope="col">Tanggal</th>
                    <th scope="col">Kegiatan</th>
                    <th scope="col">Rute</th>
                    <th scope="col" class="ta-r">Total</th>
                    <th scope="col">SOJ</th>
                    <th scope="col">MMJ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stos as $sto)
                    <tr data-quick-row>
                        <td data-label="Nomor STO"><button type="button" class="sto-no link-btn" data-dialog-open="sto-{{ $sto->id }}">{{ $sto->number }}</button></td>
                        <td data-label="Tanggal" class="num" style="white-space:nowrap">{{ $sto->document_date->format('d/m/Y') }}</td>
                        <td data-label="Kegiatan"><span class="badge {{ $sto->activity === 'BONGKAR' ? 'badge-blue' : 'badge-teal' }}">{{ $sto->activity }}</span></td>
                        <td data-label="Rute" class="quick-route"><span title="{{ $sto->origin }} → {{ $sto->destination }}">{{ \Illuminate\Support\Str::limit(strtok($sto->origin, "\n"), 28) }} → {{ \Illuminate\Support\Str::limit(strtok($sto->destination, "\n"), 28) }}</span></td>
                        <td data-label="Total" class="total ta-r">{{ $rupiah($sto->total) }}</td>
                        @foreach(['soj' => 'SOJ', 'mmj' => 'MMJ'] as $field => $label)
                            <td data-label="{{ $label }}" class="quick-cell">
                                <input type="hidden" name="refs[{{ $sto->id }}][{{ $field }}_awal]" value="{{ $sto->{$field} }}">
                                <input class="input input-sm {{ blank($sto->{$field}) ? 'is-empty' : '' }}" name="refs[{{ $sto->id }}][{{ $field }}]" value="{{ old("refs.{$sto->id}.{$field}", $sto->{$field}) }}" maxlength="100" autocomplete="off" placeholder="Isi {{ $label }}" aria-label="{{ $label }} STO {{ $sto->number }}" data-quick-input data-initial="{{ $sto->{$field} }}">
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="quick-bar">
        <span class="quick-status" data-quick-status aria-live="polite">Tekan Enter untuk pindah ke kolom berikutnya.</span>
        <button type="submit" class="btn btn-cta" data-submit data-loading-text="Menyimpan…"><x-icon name="check" :size="18" /><span>Simpan semua</span></button>
    </div>
</form>
