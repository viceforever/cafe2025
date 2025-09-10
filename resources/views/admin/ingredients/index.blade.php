@extends('layouts.app')

@section('content')
<!-- Added padding-top to prevent navigation overlap -->
<div class="container" style="padding-top: 100px;">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Управление ингредиентами</h2>
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
                                            <form action="{{ route('admin.ingredients.update-quantity', $ingredient) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <div class="input-group input-group-sm" style="width: 120px;">
                                                    <input type="number" step="0.01" name="quantity" 
                                                           value="{{ $ingredient->quantity }}" class="form-control">
                                                    <button type="submit" class="btn btn-outline-primary btn-sm">
                                                        <i class="iconify" data-icon="mdi:check"></i>
                                                    </button>
                                                </div>
                                            </form>
                                        </td>
                                        <td>{{ $ingredient->min_quantity }} {{ $ingredient->unit }}</td>
                                        <td>{{ number_format($ingredient->cost_per_unit, 2) }} ₽</td>
                                        <td>
                                            @if($ingredient->isLowStock())
                                                <span class="badge bg-warning">Низкий остаток</span>
                                            @else
                                                <span class="badge bg-success">В наличии</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.ingredients.edit', $ingredient) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="iconify" data-icon="mdi:pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.ingredients.destroy', $ingredient) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Удалить ингредиент?')">
                                                    <i class="iconify" data-icon="mdi:delete"></i>
                                                </button>
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
                    
                    {{ $ingredients->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
