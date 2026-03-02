@if ($paginator->hasPages())
    <nav aria-label="Pagination">
        <ul class="pagination">
            {{-- ปุ่ม "Previous" --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo;</a>
                </li>
            @endif

            {{-- หน้าแรก --}}
            @if ($paginator->currentPage() > 3)
                <li class="page-item"><a class="page-link" href="{{ $paginator->url(1) }}">1</a></li>
                @if ($paginator->currentPage() > 4)
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                @endif
            @endif

            {{-- 5 หน้าใกล้เคียง --}}
            @foreach (range(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page)
                <li class="page-item {{ $page == $paginator->currentPage() ? 'active' : '' }}">
                    <a class="page-link" href="{{ $paginator->url($page) }}">{{ $page }}</a>
                </li>
            @endforeach

            {{-- หน้าสุดท้าย --}}
            @if ($paginator->currentPage() < $paginator->lastPage() - 2)
                @if ($paginator->currentPage() < $paginator->lastPage() - 3)
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                @endif
                <li class="page-item"><a class="page-link" href="{{ $paginator->url($paginator->lastPage()) }}">{{ $paginator->lastPage() }}</a></li>
            @endif

            {{-- ปุ่ม "Next" --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">&raquo;</a>
                </li>
            @else
                <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
            @endif
        </ul>
    </nav>
@endif