@if ($paginator->hasPages())
    <nav class="d-flex justify-content-between align-items-center w-100 my-3" role="navigation" aria-label="Simple Pagination Navigation">
        @if ($paginator->onFirstPage())
            <span class="btn btn-outline-secondary btn-sm disabled rounded-pill px-3">&laquo; Previous</span>
        @else
            <a class="btn btn-outline-success btn-sm rounded-pill px-3" href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo; Previous</a>
        @endif

        @if ($paginator->hasMorePages())
            <a class="btn btn-outline-success btn-sm rounded-pill px-3" href="{{ $paginator->nextPageUrl() }}" rel="next">Next &raquo;</a>
        @else
            <span class="btn btn-outline-secondary btn-sm disabled rounded-pill px-3">Next &raquo;</span>
        @endif
    </nav>
@endif
