@if ($paginator->hasPages())
    <nav class="shop-pager" role="navigation" aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <span class="shop-pager__btn is-disabled" aria-disabled="true">Previous</span>
        @else
            <a class="shop-pager__btn" href="{{ $paginator->previousPageUrl() }}" rel="prev">Previous</a>
        @endif

        <ul class="shop-pager__pages">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="shop-pager__ellipsis">…</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li>
                            @if ($page == $paginator->currentPage())
                                <span class="shop-pager__page is-active" aria-current="page">{{ $page }}</span>
                            @else
                                <a class="shop-pager__page" href="{{ $url }}">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach
        </ul>

        @if ($paginator->hasMorePages())
            <a class="shop-pager__btn" href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a>
        @else
            <span class="shop-pager__btn is-disabled" aria-disabled="true">Next</span>
        @endif
    </nav>
@endif
