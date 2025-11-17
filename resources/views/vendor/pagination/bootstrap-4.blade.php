@if ($paginator->hasPages())
    <nav>
        <ul class=" js-font-resize pagination" style="overflow: scroll;">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class=" js-font-resize page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <span class=" js-font-resize page-link" aria-hidden="true">&lsaquo;</span>
                </li>
            @else
                <li class=" js-font-resize page-item">
                    <a class=" js-font-resize page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">&lsaquo;</a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class=" js-font-resize page-item disabled" aria-disabled="true"><span class=" js-font-resize page-link">{{ $element }}</span></li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class=" js-font-resize page-item active" aria-current="page"><span class=" js-font-resize page-link">{{ $page }}</span></li>
                        @else
                            <li class=" js-font-resize page-item"><a class=" js-font-resize page-link" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class=" js-font-resize page-item">
                    <a class=" js-font-resize page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">&rsaquo;</a>
                </li>
            @else
                <li class=" js-font-resize page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    <span class=" js-font-resize page-link" aria-hidden="true">&rsaquo;</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
