@if ($paginator->hasPages())
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        $pages = [];

        if ($lastPage <= 7) {
            $pages = range(1, $lastPage);
        } else {
            $pages = [1, 2];

            if ($currentPage > 3) {
                $pages[] = '...';
            }

            if ($currentPage > 2 && $currentPage < $lastPage) {
                $pages[] = $currentPage - 1;
                $pages[] = $currentPage;
            }

            if ($currentPage < $lastPage - 2) {
                $pages[] = '...';
            }

            $pages[] = $lastPage - 1;
            $pages[] = $lastPage;
            $pages = array_values(array_unique($pages, SORT_REGULAR));
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
                    <a href="{{ $paginator->url($page) }}" class="web-page-btn">{{ $page }}</a>
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
