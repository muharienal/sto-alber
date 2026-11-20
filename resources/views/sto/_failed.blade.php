{{--
    STO yang gagal dibaca dari PDF dan belum ditambahkan manual (milik pengguna yang sedang masuk).
    Tombol "Tambah manual" membuka form tambah yang sudah terisi dari bagian yang terbaca.
    Parameter: $failedRows (koleksi ['batch' => ImportBatch, 'row' => array])
--}}
<section class="card failed-card" aria-labelledby="failed-panel-title">
    <div class="card-head">
        <div class="card-title">
            <span class="chip-icon tone-red"><x-icon name="file-alert" :size="20" /></span>
            <div>
                <h2 id="failed-panel-title">{{ $failedRows->count() }} STO gagal dibaca, perlu ditambahkan manual</h2>
                <p>Form sudah terisi dari bagian yang terbaca. Lengkapi sisanya dengan melihat halaman PDF-nya.</p>
            </div>
        </div>
    </div>
    @foreach($failedRows->groupBy(fn ($f) => $f['batch']->id) as $group)
        @php $batch = $group->first()['batch']; @endphp
        <div class="failed-group">
            <div class="failed-group-head">
                <span><x-icon name="file" :size="15" /> {{ $batch->original_name }} · diimpor {{ $batch->updated_at->format('d/m/Y H:i') }}</span>
                <form method="post" action="{{ route('sto.failed.dismiss', $batch) }}">@csrf<button type="submit" class="btn btn-ghost btn-sm" title="Sembunyikan daftar dari PDF ini">Tandai selesai</button></form>
            </div>
            <ul class="failed-list">
                @foreach($group as $f)
                    @php
                        $row = $f['row'];
                        $prefill = [
                            'number' => $row['number'],
                            'document_date' => $row['document_date'] ?? '',
                            'activity' => $row['activity'] ?? '',
                            'origin' => $row['origin'] ?? '',
                            'destination' => $row['destination'] ?? '',
                            'items' => array_map(fn ($i) => [
                                'name' => $i['name'] ?? '',
                                'quantity' => isset($i['quantity']) && $i['quantity'] !== '' ? rtrim(rtrim($i['quantity'], '0'), '.') : '',
                                'unit' => $i['unit'] ?? 'TON',
                                'rate' => isset($i['rate']) && $i['rate'] !== '' ? rtrim(rtrim($i['rate'], '0'), '.') : '',
                            ], $row['items'] ?? []),
                            'import_batch_id' => $batch->id,
                            'source_page' => $row['source_page'],
                            'source' => $batch->original_name.' · halaman '.$row['source_page'],
                            'pdf' => route('sto.batch-pdf', $batch).'#page='.$row['source_page'],
                            'message' => $row['message'] ?? '',
                        ];
                    @endphp
                    <li>
                        <span class="sto-no">{{ $row['number'] }}</span>
                        <span class="fl-msg">{{ $row['message'] ?? 'Gagal dibaca' }}</span>
                        <span class="fl-actions">
                            <a class="btn btn-ghost btn-sm" href="{{ $prefill['pdf'] }}" target="_blank" rel="noopener"><x-icon name="external" :size="15" />Halaman {{ $row['source_page'] }}</a>
                            <button type="button" class="btn btn-secondary btn-sm" data-dialog-open="create-sto" data-prefill="{{ json_encode($prefill, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}"><x-icon name="plus" :size="15" />Tambah manual</button>
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
</section>
