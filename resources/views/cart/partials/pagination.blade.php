@if ($paginatedCart->total() > 0)
    <div class="d-flex justify-content-between align-items-center mt-4">
        <div>
            <p class="text-muted mb-0">
                Показано {{ $paginatedCart->firstItem() }} - {{ $paginatedCart->lastItem() }} из {{ $paginatedCart->total() }} товаров
            </p>
        </div>
        <div>
            @if ($paginatedCart->hasPages())
                <nav>
                    <ul class="pagination mb-0">
                        @if ($paginatedCart->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link">Предыдущая</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $paginatedCart->previousPageUrl() }}">Предыдущая</a>
                            </li>
                        @endif

                        @if ($paginatedCart->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $paginatedCart->nextPageUrl() }}">Следующая</a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-link">Следующая</span>
                            </li>
                        @endif
                    </ul>
                </nav>
            @endif
        </div>
    </div>
@endif
