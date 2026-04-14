@if ($paginator->hasPages())
    <div class="pagination-ptcg">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="page-item-ptcg disabled"><i class="bi bi-chevron-left"></i></span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="page-item-ptcg"><i class="bi bi-chevron-left"></i></a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span class="page-item-ptcg disabled">{{ $element }}</span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="page-item-ptcg active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="page-item-ptcg">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="page-item-ptcg"><i class="bi bi-chevron-right"></i></a>
        @else
            <span class="page-item-ptcg disabled"><i class="bi bi-chevron-right"></i></span>
        @endif
    </div>
@endif
