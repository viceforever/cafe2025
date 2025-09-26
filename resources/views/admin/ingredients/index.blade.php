@extends('layouts.app')

@section('content')
<!-- Added padding-top to prevent navigation overlap -->
<div class="container" style="padding-top: 200px;">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Управление ингредиентами</h2>
                <!-- Убрал модальное окно, оставил только простую кнопку -->
                <a href="{{ route('admin.ingredients.create') }}" class="btn btn-primary">
                    <i class="iconify" data-icon="mdi:plus"></i> Добавить ингредиент
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($lowStockIngredients->count() > 0)
                <div class="alert alert-warning">
                    <h5><i class="iconify" data-icon="mdi:alert"></i> Низкие остатки:</h5>
                    <ul class="mb-0">
                        @foreach($lowStockIngredients as $ingredient)
                            <li>{{ $ingredient->name }} - {{ $ingredient->quantity }} {{ $ingredient->unit }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Название</th>
                                    <th>Единица</th>
                                    <th>Остаток</th>
                                    <th>Мин. остаток</th>
                                    <th>Стоимость за ед.</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ingredients as $ingredient)
                                    <tr class="{{ $ingredient->isLowStock() ? 'table-warning' : '' }}">
                                        <td>{{ $ingredient->name }}</td>
                                        <td>{{ $ingredient->unit }}</td>
                                        <td>
                                            <!-- убрал кнопку сохранения, добавил сохранение по Enter -->
                                            <form action="{{ route('admin.ingredients.update-quantity', $ingredient) }}" 
                                                  method="POST" class="d-inline quantity-form">
                                                @csrf
                                                @method('PATCH')
                                                <input type="number" step="0.01" name="quantity" 
                                                       value="{{ $ingredient->quantity }}" 
                                                       class="form-control form-control-sm quantity-input" 
                                                       style="width: 80px; display: inline-block;">
                                            </form>
                                        </td>
                                        <td>{{ $ingredient->min_quantity }} {{ $ingredient->unit }}</td>
                                        <td>{{ number_format($ingredient->cost_per_unit, 2) }} ₽</td>
                                        <td>
                                            @if($ingredient->isLowStock())
                                                <!-- изменил цвет с bg-dark на bg-danger для лучшей видимости -->
                                                <!-- Здесь можно поменять цвет статуса: bg-dark (темный), bg-danger (красный), bg-warning (желтый) -->
                                                <span class="badge bg-danger text-white">Низкий остаток</span>
                                            @else
                                                <!-- изменил цвет с bg-primary на bg-success для лучшей видимости -->
                                                <!-- Здесь можно поменять цвет статуса: bg-primary (синий), bg-success (зеленый), bg-info (голубой) -->
                                                <span class="badge bg-success text-white">В наличии</span>
                                            @endif
                                        </td>
                                        <td>
                                            <!-- изменил стиль кнопок как на странице продуктов -->
                                            <a href="{{ route('admin.ingredients.edit', $ingredient) }}" 
                                               class="btn btn-sm btn-primary">Редактировать</a>
                                            <form action="{{ route('admin.ingredients.destroy', $ingredient) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Вы уверены?')">Удалить</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Ингредиенты не найдены</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- заменил стандартную пагинацию на кастомную русскую --}}
                    {{ $ingredients->links('custom.pagination') }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- добавил JavaScript для сохранения по Enter -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const quantityInputs = document.querySelectorAll('.quantity-input');
    
    quantityInputs.forEach(function(input) {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.closest('form').submit();
            }
        });
    });
});
</script>

@endsection
