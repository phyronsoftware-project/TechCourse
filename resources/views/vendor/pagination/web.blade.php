@if ($paginator->hasPages())
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        $pages = [];

        // Show the first, current neighbors, and last page with gaps like the requested example.
        $visiblePages = [1, $lastPage];
        for ($page = max(1, $currentPage - 1); $page <= min($lastPage, $currentPage + 1); $page++) {
            $visiblePages[] = $page;
        }
        $visiblePages = array_values(array_unique($visiblePages));
        sort($visiblePages);
        $previousVisiblePage = 0;
        foreach ($visiblePages as $page) {
            if ($previousVisiblePage && $page - $previousVisiblePage === 2) {
                $pages[] = $previousVisiblePage + 1;
            } elseif ($previousVisiblePage && $page - $previousVisiblePage > 2) {
                $pages[] = '...';
            }
            $pages[] = $page;
            $previousVisiblePage = $page;
        }
    @endphp

    <nav role="navigation" aria-label="Pagination Navigation" class="web-pagination-wrap">
        @if ($paginator->onFirstPage())
            <span class="web-page-btn is-disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                <i class="fa-solid fa-chevron-left"></i>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="web-page-btn" aria-label="@lang('pagination.previous')">
                <i class="fa-solid fa-chevron-left"></i>
            </a>
        @endif

        <div class="web-pagination-pages">
            @foreach ($pages as $page)
                @if ($page === '...')
                    <span class="web-page-btn is-muted" aria-disabled="true">...</span>
                @elseif ($page == $currentPage)
                    <span class="web-page-btn is-active" aria-current="page">{{ $page }}</span>
                @else
                    <a href="{{ $paginator->url($page) }}" class="web-page-btn" aria-label="{{ __('Page :page', ['page' => $page]) }}">{{ $page }}</a>
                @endif
            @endforeach
        </div>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="web-page-btn" aria-label="@lang('pagination.next')">
                <i class="fa-solid fa-chevron-right"></i>
            </a>
        @else
            <span class="web-page-btn is-disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                <i class="fa-solid fa-chevron-right"></i>
            </span>
        @endif
    </nav>
@endif
