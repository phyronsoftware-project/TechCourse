@if ($paginator->hasPages())
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        $pages = [];

        if ($lastPage <= 7) {
            $pages = range(1, $lastPage);
        } else {
            $pages = [1, 2];

            if ($currentPage <= 3) {
                $pages[] = 3;
                $pages[] = '...';
            } elseif ($currentPage >= $lastPage - 2) {
                $pages[] = '...';
                $pages[] = $lastPage - 2;
            } else {
                $pages[] = '...';
                $pages[] = $currentPage - 1;
                $pages[] = $currentPage;
                $pages[] = $currentPage + 1;
                $pages[] = '...';
            }

            $pages[] = $lastPage - 1;
            $pages[] = $lastPage;

            $pages = array_values(array_unique(array_filter($pages, static function ($page) use ($lastPage) {
                return $page === '...' || ($page >= 1 && $page <= $lastPage);
            }), SORT_REGULAR));
        }
    @endphp

    <nav role="navigation" aria-label="Pagination Navigation" class="admin-pagination-wrap">
        <div class="admin-pagination-meta">
            <span>{{ $paginator->firstItem() ?? 0 }} to {{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }} Items</span>
        </div>

        <div class="admin-pagination">
            @if ($paginator->onFirstPage())
                <span class="admin-page-btn is-disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m12.5 4.5-5 5 5 5" />
                    </svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="admin-page-btn" aria-label="@lang('pagination.previous')">
                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m12.5 4.5-5 5 5 5" />
                    </svg>
                </a>
            @endif

            @foreach ($pages as $page)
                @if ($page === '...')
                    <span class="admin-page-btn is-muted" aria-disabled="true">{{ $page }}</span>
                @elseif ($page == $currentPage)
                    <span class="admin-page-btn is-active" aria-current="page">{{ $page }}</span>
                @else
                    <a href="{{ $paginator->url($page) }}" class="admin-page-btn">{{ $page }}</a>
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="admin-page-btn" aria-label="@lang('pagination.next')">
                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m7.5 4.5 5 5-5 5" />
                    </svg>
                </a>
            @else
                <span class="admin-page-btn is-disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m7.5 4.5 5 5-5 5" />
                    </svg>
                </span>
            @endif
        </div>
    </nav>
@endif
