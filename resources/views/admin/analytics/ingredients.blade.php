@extends('layouts.app')

@section('content')
<div class="container" style="padding-top: 220px;">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Аналитика ингредиентов</h2>
                <a href="{{ route('admin.analytics.index') }}" class="btn btn-secondary">
                    <i class="iconify" data-icon="mdi:arrow-left"></i> Назад к общей аналитике
                </a>
            </div>

            <!-- Предупреждения о низких остатках -->
            @if($lowStockIngredients->count() > 0)
                <div class="alert alert-warning mb-4">
                    <h5><i class="iconify" data-icon="mdi:alert"></i> Внимание! Низкие остатки</h5>
                    <p>У {{ $lowStockIngredients->count() }} ингредиентов остатки ниже минимального уровня.</p>
                </div>
            @endif

            <div class="row">
                <!-- Ингредиенты с низкими остатками -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h5><i class="iconify" data-icon="mdi:alert-circle"></i> Низкие остатки</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Ингредиент</th>
                                            <th>Остаток</th>
                                            <th>Минимум</th>
                                            <th>Статус</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($lowStockIngredients as $ingredient)
                                            <tr>
                                                <td>{{ $ingredient->name }}</td>
                                                <td>{{ number_format($ingredient->quantity, 2) }} {{ $ingredient->unit }}</td>
                                                <td>{{ number_format($ingredient->min_quantity, 2) }} {{ $ingredient->unit }}</td>
                                                <td>
                                                    <span class="badge bg-danger">
                                                        Критично
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-success">
                                                    <i class="iconify" data-icon="mdi:check-circle"></i> 
                                                    Все ингредиенты в достатке
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Самые дорогие ингредиенты -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5><i class="iconify" data-icon="mdi:currency-usd"></i> Самые дорогие ингредиенты</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Место</th>
                                            <th>Ингредиент</th>
                                            <th>Цена за единицу</th>
                                            <th>Остаток</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($expensiveIngredients as $index => $ingredient)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-{{ $index < 3 ? ['warning', 'secondary', 'dark'][$index] : 'light text-dark' }}">
                                                        {{ $index + 1 }}
                                                    </span>
                                                </td>
                                                <td>{{ $ingredient->name }}</td>
                                                <td>{{ number_format($ingredient->cost_per_unit, 2) }} ₽/{{ $ingredient->unit }}</td>
                                                <td>{{ number_format($ingredient->quantity, 2) }} {{ $ingredient->unit }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center">Нет данных</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Использование ингредиентов за последние 30 дней -->
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5><i class="iconify" data-icon="mdi:chart-line"></i> Использование ингредиентов (последние 30 дней)</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Ингредиент</th>
                                            <th>Использовано</th>
                                            <th>Остаток</th>
                                            <th>Стоимость единицы</th>
                                            <th>Общая стоимость использования</th>
                                            <th>Статус остатка</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($ingredientUsage->sortByDesc('usage_last_30_days') as $ingredient)
                                            @php
                                                $usageValue = $ingredient->usage_last_30_days ?? 0;
                                                $totalCost = $usageValue * $ingredient->cost_per_unit;
                                                $stockStatus = $ingredient->quantity <= $ingredient->min_quantity ? 'danger' : 
                                                              ($ingredient->quantity <= $ingredient->min_quantity * 2 ? 'warning' : 'success');
                                            @endphp
                                            <tr>
                                                <td>{{ $ingredient->name }}</td>
                                                <td>{{ number_format($usageValue, 2) }} {{ $ingredient->unit }}</td>
                                                <td>{{ number_format($ingredient->quantity, 2) }} {{ $ingredient->unit }}</td>
                                                <td>{{ number_format($ingredient->cost_per_unit, 2) }} ₽</td>
                                                <td>{{ number_format($totalCost, 2) }} ₽</td>
                                                <td>
                                                    <span class="badge bg-{{ $stockStatus }}">
                                                        @if($stockStatus === 'danger')
                                                            Критично
                                                        @elseif($stockStatus === 'warning')
                                                            Мало
                                                        @else
                                                            Норма
                                                        @endif
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">Нет данных об использовании</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Сводная статистика -->
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="iconify" data-icon="mdi:chart-pie"></i> Сводная статистика</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body">
                                            <h3>{{ $ingredientUsage->count() }}</h3>
                                            <p class="mb-0">Всего ингредиентов</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-danger text-white">
                                        <div class="card-body">
                                            <h3>{{ $lowStockIngredients->count() }}</h3>
                                            <p class="mb-0">Критичные остатки</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-success text-white">
                                        <div class="card-body">
                                            <h3>{{ number_format($ingredientUsage->sum('usage_last_30_days'), 0) }}</h3>
                                            <p class="mb-0">Использовано за месяц</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-info text-white">
                                        <div class="card-body">
                                            <h3>{{ number_format($ingredientUsage->sum(function($ingredient) { return ($ingredient->usage_last_30_days ?? 0) * $ingredient->cost_per_unit; }), 0) }} ₽</h3>
                                            <p class="mb-0">Стоимость использования</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
