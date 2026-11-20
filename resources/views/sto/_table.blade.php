{{-- Tabel (desktop) dan kartu (HP) Master STO. Parameter: $stos, $rupiah, $qty --}}
    {{-- Tabel (desktop) --}}
    <div class="desk-only">
        <div class="table-scroll" tabindex="0" role="region" aria-label="Tabel STO">
            <table class="data-table">
                <thead>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Nomor STO</th>
                        <th scope="col">Tanggal</th>
                        <th scope="col">Kegiatan</th>
                        <th scope="col">Asal</th>
                        <th scope="col">Tujuan</th>
                        <th scope="col">Nama Barang</th>
                        <th scope="col" class="ta-r">Qty</th>
                        <th scope="col" class="ta-r">Tarif</th>
                        <th scope="col" class="ta-r">Total</th>
                        <th scope="col">SOJ / MMJ</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                @foreach($stos as $sto)
                    @php
                        $items = $sto->items->values();
                        $span = max(1, $items->count());
                        $no = $stos->firstItem() + $loop->index;
                    @endphp
                    <tbody class="group">
                        @for($i = 0; $i < $span; $i++)
                            @php $item = $items[$i] ?? null; @endphp
                            <tr>
                                @if($i === 0)
                                    <td rowspan="{{ $span }}" class="col-no num">{{ $no }}</td>
                                    <td rowspan="{{ $span }}"><button type="button" class="sto-no link-btn" data-dialog-open="sto-{{ $sto->id }}">{{ $sto->number }}</button></td>
                                    <td rowspan="{{ $span }}" class="num" style="white-space:nowrap">{{ $sto->document_date->format('d/m/Y') }}</td>
                                    <td rowspan="{{ $span }}"><span class="badge {{ $sto->activity === 'BONGKAR' ? 'badge-blue' : 'badge-teal' }}">{{ $sto->activity }}</span></td>
                                    <td rowspan="{{ $span }}" class="place"><div class="clamp" title="{{ $sto->origin }}">{{ $sto->origin }}</div></td>
                                    <td rowspan="{{ $span }}" class="place"><div class="clamp" title="{{ $sto->destination }}">{{ $sto->destination }}</div></td>
                                @endif
                                <td class="item name">{{ $item?->name ?? '—' }}</td>
                                <td class="item ta-r qty">@if($item){{ $qty($item->quantity) }} <span class="unit">{{ $item->unit }}</span>@else — @endif</td>
                                <td class="item ta-r">{{ $item ? $rupiah($item->rate) : '—' }}</td>
                                @if($i === 0)
                                    <td rowspan="{{ $span }}" class="total ta-r">{{ $rupiah($sto->total) }}</td>
                                    <td rowspan="{{ $span }}" class="refs">
                                        <div class="ref"><small>SOJ</small>@if(filled($sto->soj))<span>{{ $sto->soj }}</span>@else<span class="badge badge-amber">Kosong</span>@endif</div>
                                        <div class="ref"><small>MMJ</small>@if(filled($sto->mmj))<span>{{ $sto->mmj }}</span>@else<span class="badge badge-amber">Kosong</span>@endif</div>
                                    </td>
                                    <td rowspan="{{ $span }}">
                                        <div class="row-actions">
                                            <button type="button" class="icon-btn act" data-dialog-open="sto-{{ $sto->id }}" title="Detail STO" aria-label="Detail STO {{ $sto->number }}"><x-icon name="eye" :size="17" /></button>
                                            @if($sto->batch && $sto->source_page)
                                                <a class="icon-btn act" target="_blank" rel="noopener" href="{{ route('sto.pdf', $sto) }}#page={{ $sto->source_page }}" title="Buka PDF sumber" aria-label="Buka PDF STO {{ $sto->number }}"><x-icon name="external" :size="17" /></a>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endfor
                    </tbody>
                @endforeach
            </table>
        </div>
    </div>

    {{-- Kartu (HP & tablet kecil) --}}
    <div class="sto-cards" aria-label="Daftar STO">
        @foreach($stos as $sto)
            <article class="sto-card">
                <div class="top">
                    <button type="button" class="sto-no link-btn" data-dialog-open="sto-{{ $sto->id }}">{{ $sto->number }}</button>
                    <span class="date num">{{ $sto->document_date->format('d/m/Y') }}</span>
                </div>
                <div class="badges">
                    <span class="badge {{ $sto->activity === 'BONGKAR' ? 'badge-blue' : 'badge-teal' }}">{{ $sto->activity }}</span>
                    @if($sto->missingReferences())
                        <span class="badge badge-amber">{{ implode(' & ', $sto->missingReferences()) }} kosong</span>
                    @endif
                </div>
                <div class="route">
                    <div><small>Asal</small><p>{{ $sto->origin }}</p></div>
                    <div><small>Tujuan</small><p>{{ $sto->destination }}</p></div>
                </div>
                <div class="route">
                    <div><small>SOJ</small><p>{{ filled($sto->soj) ? $sto->soj : '—' }}</p></div>
                    <div><small>MMJ</small><p>{{ filled($sto->mmj) ? $sto->mmj : '—' }}</p></div>
                </div>
                <ul class="items">
                    @foreach($sto->items as $item)
                        <li>
                            <b>{{ $item->name }}</b>
                            <span>{{ $qty($item->quantity) }} {{ $item->unit }}<br>× {{ $rupiah($item->rate) }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="sum"><span>Total</span><strong>Rp {{ $rupiah($sto->total) }}</strong></div>
                <div class="actions">
                    <button type="button" class="btn btn-secondary btn-sm" data-dialog-open="sto-{{ $sto->id }}">Detail</button>
                    @if($sto->batch && $sto->source_page)
                        <a class="btn btn-secondary btn-sm" target="_blank" rel="noopener" href="{{ route('sto.pdf', $sto) }}#page={{ $sto->source_page }}"><x-icon name="external" :size="15" />PDF</a>
                    @endif
                </div>
            </article>
        @endforeach
    </div>

