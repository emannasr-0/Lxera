@if ($paginator->hasPages())
    <div class=" js-font-resize card-footer text-right">
        <nav class=" js-font-resize d-inline-block ">
            <ul class=" js-font-resize pagination mb-0">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class=" js-font-resize page-item  disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                        <span aria-hidden="true">&lsaquo;</span>
                    </li>
                @else
                    <li class=" js-font-resize page-item ">
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">&lsaquo;</a>
                    </li>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <li class=" js-font-resize page-item disabled" aria-disabled="true"><span>{{ $element }}</span></li>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class=" js-font-resize page-item active" aria-current="page"><span>{{ $page }}</span></li>
                            @else
                                <li class=" js-font-resize page-item "><a href="{{ $url }}">{{ $page }}</a></li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class=" js-font-resize page-item ">
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">&rsaquo;</a>
                    </li>
                @else
                    <li class=" js-font-resize page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                        <span aria-hidden="true">&rsaquo;</span>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
@endif
