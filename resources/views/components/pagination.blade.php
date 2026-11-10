@if($paginator->hasPages())
@php
    $current = $paginator->currentPage();
    $last = $paginator->lastPage();
    $from = max(1, $current - 2);
    $to = min($last, $current + 2);
@endphp
<nav class="pager" aria-label="Halaman data">
    <span class="pager-info">Halaman {{ $current }} dari {{ $last }} · {{ number_format($paginator->total(), 0, ',', '.') }} STO</span>
    <div class="pager-links">
        @if($paginator->onFirstPage())
            <span class="page-link is-wide is-disabled" aria-disabled="true"><x-icon name="chevron-left" :size="16" />Sebelumnya</span>
        @else
            <a class="page-link is-wide" href="{{ $paginator->previousPageUrl() }}" rel="prev"><x-icon name="chevron-left" :size="16" />Sebelumnya</a>
        @endif
        @if($from > 1)
            <a class="page-link" href="{{ $paginator->url(1) }}">1</a>
            @if($from > 2)<span class="page-link is-disabled">…</span>@endif
        @endif
        @for($page = $from; $page <= $to; $page++)
            @if($page === $current)
                <span class="page-link is-current" aria-current="page">{{ $page }}</span>
            @else
                <a class="page-link" href="{{ $paginator->url($page) }}">{{ $page }}</a>
            @endif
        @endfor
        @if($to < $last)
            @if($to < $last - 1)<span class="page-link is-disabled">…</span>@endif
            <a class="page-link" href="{{ $paginator->url($last) }}">{{ $last }}</a>
        @endif
        @if($paginator->hasMorePages())
            <a class="page-link is-wide" href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya<x-icon name="chevron-right" :size="16" /></a>
        @else
            <span class="page-link is-wide is-disabled" aria-disabled="true">Berikutnya<x-icon name="chevron-right" :size="16" /></span>
        @endif
    </div>
</nav>
@endif
