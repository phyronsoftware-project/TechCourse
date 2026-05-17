@if ($paginator->hasPages())
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

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="admin-page-btn is-muted" aria-disabled="true">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="admin-page-btn is-active" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="admin-page-btn">{{ $page }}</a>
                        @endif
                    @endforeach
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
