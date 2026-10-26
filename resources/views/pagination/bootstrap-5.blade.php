@if ($paginator->hasPages())
    <nav class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 w-100 my-3" role="navigation" aria-label="Pagination Navigation">
        <div class="text-muted small">
            Showing 
            <span class="fw-semibold text-dark">{{ $paginator->firstItem() ?? 0 }}</span>
            to 
            <span class="fw-semibold text-dark">{{ $paginator->lastItem() ?? 0 }}</span>
            of 
            <span class="fw-semibold text-dark">{{ $paginator->total() }}</span>
            results
        </div>

        <ul class="pagination pagination-sm m-0 gap-1 align-items-center list-unstyled">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <span class="page-link rounded-3 px-3 py-2 border-0 bg-light text-muted" aria-hidden="true">&laquo;</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link rounded-3 px-3 py-2 border text-dark fw-semibold" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">&laquo;</a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link rounded-3 px-3 py-2 border-0 bg-transparent text-muted">{{ $element }}</span>
                    </li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page">
                                <span class="page-link rounded-3 px-3 py-2 fw-bold text-white shadow-sm" style="background-color: #059669; border-color: #059669;">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link rounded-3 px-3 py-2 border text-dark fw-semibold" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link rounded-3 px-3 py-2 border text-dark fw-semibold" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">&raquo;</a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    <span class="page-link rounded-3 px-3 py-2 border-0 bg-light text-muted" aria-hidden="true">&raquo;</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
