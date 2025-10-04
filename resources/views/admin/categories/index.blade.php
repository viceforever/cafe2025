@extends('layouts.app')

@section('title', 'Управление категориями')

@section('main_content')
<div class="container min-vh-100 d-flex flex-column">
    <div class="row flex-grow-1" style="margin-top: 220px; margin-bottom: 50px;">
        <div class="col-12">
            <h1>Управление категориями</h1>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary mb-3">Создать новую категорию</a>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Название</th>
                            {{-- Added column for product count --}}
                            <th>Товаров</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categories as $category)
                            <tr>
                                <td>{{ $category->id }}</td>
                                <td>{{ $category->name_category }}</td>
                                {{-- Display product count --}}
                                <td>{{ $category->products_count }}</td>
                                <td>
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-primary">Редактировать</a>
                                    <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline delete-category-form" data-category-name="{{ $category->name_category }}" data-products-count="{{ $category->products_count }}">
                                        @csrf
                                        @method('DELETE')
                                        {{-- Updated button to use custom confirmation with product count warning --}}
                                        <button type="button" class="btn btn-sm btn-danger delete-category-btn">Удалить</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Added custom confirmation modal and script --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-category-btn');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const form = this.closest('.delete-category-form');
            const categoryName = form.dataset.categoryName;
            const productsCount = parseInt(form.dataset.productsCount);
            
            let message = `Вы уверены, что хотите удалить категорию "${categoryName}"?`;
            
            if (productsCount > 0) {
                message += `\n\n⚠️ ВНИМАНИЕ: При удалении этой категории будут удалены все ${productsCount} товар(ов), входящих в неё!`;
            }
            
            if (confirm(message)) {
                form.submit();
            }
        });
    });
});
</script>
@endsection
