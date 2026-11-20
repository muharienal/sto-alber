{{-- Jendela unggah PDF STO. Terbuka otomatis bila ada error unggah, PDF yang pernah diimpor, atau ?unggah=1. --}}
@php
    $pdfErrors = $errors->get('pdf');
    $openUpload = $pdfErrors || $duplicatePdf || request()->boolean('unggah');
@endphp
<dialog class="modal modal-md" id="upload-pdf" aria-labelledby="upload-title" @if($openUpload) data-autoopen @endif>
    <div class="modal-head">
        <span class="chip-icon tone-yellow"><x-icon name="upload" :size="20" /></span>
        <div class="titles">
            <h2 id="upload-title">Unggah PDF STO</h2>
            <p>Gunakan PDF STO dari sistem (PDF teks, bukan hasil scan).</p>
        </div>
        <button type="button" class="icon-btn" data-dialog-close aria-label="Tutup"><x-icon name="x" :size="20" /></button>
    </div>
    <div class="modal-body">
        @if($duplicatePdf)
            <div class="alert alert-warn" role="alert">
                <x-icon name="alert" :size="18" />
                <div>
                    <strong>PDF ini sudah pernah diimpor.</strong>
                    <p>{{ $duplicatePdf['name'] }} identik dengan {{ $duplicatePdf['original'] }} yang diimpor {{ $duplicatePdf['imported_at'] }}{{ $duplicatePdf['by'] ? ' oleh '.$duplicatePdf['by'] : '' }} ({{ $duplicatePdf['count'] }} STO). Membaca ulang hanya akan menghasilkan duplikat.</p>
                    <div class="alert-actions">
                        <form method="post" action="{{ route('sto.upload.discard') }}">@csrf<button type="submit" class="btn btn-secondary btn-sm">Batal</button></form>
                        <form method="post" action="{{ route('sto.upload.anyway') }}" data-loading>@csrf<button type="submit" class="btn btn-ghost btn-sm" data-submit data-loading-text="Membaca PDF…"><span>Tetap baca</span></button></form>
                    </div>
                </div>
            </div>
        @endif

        @if($pdfErrors)
            <div class="alert alert-error" role="alert">
                <x-icon name="alert" :size="18" />
                <div>
                    <strong>PDF gagal diproses</strong>
                    <ul>@foreach($pdfErrors as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            </div>
        @endif

        <form method="post" action="{{ route('sto.upload') }}" enctype="multipart/form-data" data-loading data-upload-form>
            @csrf
            <div class="upload-grid">
                <label class="dropzone" data-dropzone for="pdf">
                    <input class="sr-only" type="file" id="pdf" name="pdf" accept="application/pdf,.pdf" required aria-label="Pilih PDF STO">
                    <span class="dz-icon"><x-icon name="upload" :size="28" /></span>
                    <span class="dz-title" data-dz-title>Pilih PDF STO</span>
                    <span class="dz-hint" data-dz-hint>Maks. 10 MB / 200 halaman</span>
                    <span class="btn btn-secondary btn-sm dz-btn">Pilih file atau seret ke sini</span>
                </label>

                <div class="upload-side">
                    <ol class="steps">
                        <li><div><strong>Pilih PDF</strong><br><span class="hint">Satu file boleh berisi banyak STO.</span></div></li>
                        <li><div><strong>Periksa pratinjau</strong><br><span class="hint">Belum ada yang tersimpan di tahap ini.</span></div></li>
                        <li><div><strong>Simpan</strong><br><span class="hint">Nomor STO yang sudah ada dilewati.</span></div></li>
                    </ol>

                    <button type="submit" class="btn btn-primary btn-lg btn-block" data-submit data-loading-text="Membaca PDF…">
                        <x-icon name="eye" :size="18" /><span>Baca PDF</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</dialog>
