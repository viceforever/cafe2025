@if ($paginator->hasPages())
    <nav aria-label="Навигация по страницам">
        <ul class="pagination justify-content-center">
            {{-- Кнопка "Предыдущая" --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link">
                        <i class="fas fa-chevron-left"></i>
                        <span class="d-none d-sm-inline ms-1">Предыдущая</span>
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                        <i class="fas fa-chevron-left"></i>
                        <span class="d-none d-sm-inline ms-1">Предыдущая</span>
                    </a>
                </li>
            @endif

            {{-- Элементы пагинации --}}
            @foreach ($elements as $element)
                {{-- "Три точки" --}}
                @if (is_string($element))
                    <li class="page-item disabled"><span class="page-link">{{ $element }}</span></li>
                @endif

                {{-- Массив ссылок --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active">
                                <span class="page-link">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Кнопка "Следующая" --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">
                        <span class="d-none d-sm-inline me-1">Следующая</span>
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link">
                        <span class="d-none d-sm-inline me-1">Следующая</span>
                        <i class="fas fa-chevron-right"></i>
                    </span>
                </li>
            @endif
        </ul>
    </nav>

    {{-- Информация о результатах --}}
    <div class="d-flex justify-content-center mt-2">
        <small class="text-muted">
            Показано {{ $paginator->firstItem() }} - {{ $paginator->lastItem() }} из {{ $paginator->total() }} результатов
        </small>
    </div>
@endif

<style>
.pagination .page-link {
    border: 1px solid #dee2e6;
    color: #6c757d;
    padding: 0.5rem 0.75rem;
    margin: 0 2px;
    border-radius: 0.375rem;
    transition: all 0.15s ease-in-out;
}

.pagination .page-link:hover {
    background-color: #e9ecef;
    border-color: #adb5bd;
    color: #495057;
}

.pagination .page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
}

.pagination .page-item.disabled .page-link {
    color: #6c757d;
    background-color: #fff;
    border-color: #dee2e6;
    cursor: not-allowed;
}

.pagination .page-link i {
    font-size: 0.875rem;
}

@media (max-width: 576px) {
    .pagination .page-link {
        padding: 0.375rem 0.5rem;
        font-size: 0.875rem;
    }
}
</style>
