@extends('layouts.app')

@section('content')
<div class="container" style="padding-top: 220px;">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Отчет по ингредиентам</h4>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Назад к отчетам
                    </a>
                </div>
                <div class="card-body">
                    <!-- Фильтры -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="start_date">Дата начала:</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" 
                                       value="{{ $startDate }}">
                            </div>
                            <div class="col-md-3">
                                <label for="end_date">Дата окончания:</label>
                                <input type="date" name="end_date" id="end_date" class="form-control" 
                                       value="{{ $endDate }}">
                            </div>
                            <div class="col-md-3">
                                <label for="min_quantity">Мин. количество:</label>
                                <input type="number" name="min_quantity" id="min_quantity" class="form-control" 
                                       value="{{ $minQuantity }}" min="0" step="0.1">
                            </div>
                            <div class="col-md-3">
                                <div class="form-check mt-4">
                                    <input type="checkbox" name="low_stock" id="low_stock" class="form-check-input" 
                                           value="1" {{ $lowStock ? 'checked' : '' }}>
                                    <label for="low_stock" class="form-check-label">
                                        Только низкие остатки
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Применить фильтры
                                </button>
                                
                                <!-- Кнопки экспорта -->
                                <div class="btn-group ml-2">
                                    <button type="button" class="btn btn-success dropdown-toggle" 
                                            data-toggle="dropdown">
                                        <i class="fas fa-download"></i> Экспорт
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" 
                                           href="{{ route('admin.reports.ingredients.export', ['format' => 'pdf']) }}?{{ http_build_query(request()->all()) }}">
                                            <i class="fas fa-file-pdf"></i> PDF
                                        </a>
                                        <a class="dropdown-item" 
                                           href="{{ route('admin.reports.ingredients.export', ['format' => 'excel']) }}?{{ http_build_query(request()->all()) }}">
                                            <i class="fas fa-file-excel"></i> Excel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Статистика -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5>Всего ингредиентов</h5>
                                    <h3>{{ $totalIngredients }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h5>Низкие остатки</h5>
                                    <h3>{{ $lowStockCount }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5>Общие затраты</h5>
                                    <h3>{{ number_format($totalCost, 2) }} ₽</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5>Общее использование</h5>
                                    <h3>{{ number_format($totalUsage, 2) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Таблица ингредиентов -->
                    <div class="card">
                        <div class="card-header">
                            <h5>Ингредиенты ({{ $ingredients->count() }})</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Название</th>
                                            <th>Единица</th>
                                            <th>Остаток</th>
                                            <th>Мин. остаток</th>
                                            <th>Стоимость/ед.</th>
                                            <th>Использовано</th>
                                            <th>Затраты</th>
                                            <th>Статус</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($ingredients as $ingredient)
                                        <tr class="{{ $ingredient->quantity <= $ingredient->min_quantity ? 'bg-danger' : '' }}">
                                            <td>{{ $ingredient->name }}</td>
                                            <td>{{ $ingredient->unit }}</td>
                                            <td>{{ $ingredient->quantity }}</td>
                                            <td>{{ $ingredient->min_quantity }}</td>
                                            <td>{{ number_format($ingredient->cost_per_unit, 2) }} ₽</td>
                                            <td>{{ number_format($ingredient->usage_period ?? 0, 2) }}</td>
                                            <td>{{ number_format($ingredient->cost_period ?? 0, 2) }} ₽</td>
                                            <td>
                                                @if($ingredient->quantity <= $ingredient->min_quantity)
                                                    <span class="badge bg-danger text-white">Низкий остаток</span>
                                                @else
                                                    <span class="badge bg-success text-white">В норме</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="8" class="text-center">Ингредиенты не найдены</td>
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
    </div>
</div>
@endsection
