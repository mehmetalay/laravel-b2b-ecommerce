@if ($paginator->hasPages())
    <nav class="custome-pagination">
        <ul class="pagination justify-content-center">
            <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                <a class="page-link" href="{{ $paginator->onFirstPage() ? 'javascript:;' : $paginator->previousPageUrl() }}" tabindex="-1" aria-disabled="true">
                    <i class="fa-solid fa-angles-left"></i>
                </a>
            </li>
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="page-item disabled">
                        <a class="page-link" href="javascript:;">{{ $element }}</a>
                    </li>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active">
                                <a class="page-link" href="javascript:;">{{ $page }}</a>
                            </li>
                        @else
                            <li class="page-item" aria-current="page">
                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach
            <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
                <a class="page-link" href="{{ $paginator->hasMorePages() ? $paginator->nextPageUrl() : 'javascript:;' }}">
                    <i class="fa-solid fa-angles-right"></i>
                </a>
            </li>
        </ul>
    </nav>
@endif
