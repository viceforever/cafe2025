@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Остатки ингредиентов</h2>
                <a href="{{ route('manager.dashboard') }}" class="btn btn-secondary">
                    <i class="iconify" data-icon="mdi:arrow-left"></i> Назад к панели
                </a>
            </div>

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
                                    <th>Статус</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ingredients as $ingredient)
                                    <tr class="{{ $ingredient->isLowStock() ? 'table-warning' : '' }}">
                                        <td>{{ $ingredient->name }}</td>
                                        <td>{{ $ingredient->unit }}</td>
                                        <td>{{ $ingredient->quantity }} {{ $ingredient->unit }}</td>
                                        <td>{{ $ingredient->min_quantity }} {{ $ingredient->unit }}</td>
                                        <td>
                                            @if($ingredient->isLowStock())
                                                <span class="badge bg-warning">Низкий остаток</span>
                                            @else
                                                <span class="badge bg-success">В наличии</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Ингредиенты не найдены</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
