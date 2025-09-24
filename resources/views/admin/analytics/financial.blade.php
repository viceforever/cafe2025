@extends('layouts.app')

@section('content')
<div class="container" style="padding-top: 220px;">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Финансовая аналитика</h2>
                <a href="{{ route('admin.analytics.index') }}" class="btn btn-secondary">
                    <i class="iconify" data-icon="mdi:arrow-left"></i> Назад к общей аналитике
                </a>
            </div>

            <!-- Фильтр по периоду -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="period" class="form-label">Период</label>
                            <select class="form-select" id="period" name="period" onchange="this.form.submit()">
                                <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Сегодня</option>
                                <option value="week" {{ $period === 'week' ? 'selected' : '' }}>Эта неделя</option>
                                <option value="month" {{ $period === 'month' ? 'selected' : '' }}>Этот месяц</option>
                                <option value="quarter" {{ $period === 'quarter' ? 'selected' : '' }}>Этот квартал</option>
                                <option value="year" {{ $period === 'year' ? 'selected' : '' }}>Этот год</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <small class="text-muted">
                                Период: {{ $startDate->format('d.m.Y') }} - {{ $endDate->format('d.m.Y') }}
                            </small>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row">
                <!-- Месячная выручка -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Выручка по месяцам</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Месяц</th>
                                            <th>Выручка</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($monthlyRevenue as $month)
                                            <tr>
                                                @php
                                                    $monthNames = [
                                                        1 => 'Январь', 2 => 'Февраль', 3 => 'Март', 4 => 'Апрель',
                                                        5 => 'Май', 6 => 'Июнь', 7 => 'Июль', 8 => 'Август',
                                                        9 => 'Сентябрь', 10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь'
                                                    ];
                                                @endphp
                                                <td>{{ $monthNames[$month->month] }} {{ $month->year }}</td>
                                                <td>{{ number_format($month->revenue, 0) }} ₽</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="text-center">Нет данных</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Структура затрат -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Структура затрат на ингредиенты</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Ингредиент</th>
                                            <th>Использовано</th>
                                            <th>Затраты</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($costBreakdown->take(10) as $item)
                                            <tr>
                                                <td>{{ $item['ingredient']->name }}</td>
                                                <td>{{ number_format($item['used_quantity'], 2) }} {{ $item['ingredient']->unit }}</td>
                                                <td>{{ number_format($item['cost'], 0) }} ₽</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center">Нет данных</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Рентабельность -->
            <div class="card">
                <div class="card-header">
                    <h5>Показатели рентабельности</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <h3 class="text-primary">{{ number_format($profitMargin, 1) }}%</h3>
                            <p class="text-muted">Маржа прибыли</p>
                        </div>
                        <div class="col-md-4">
                            <h3 class="text-dark">{{ number_format($costBreakdown->sum('cost'), 0) }} ₽</h3>
                            <p class="text-muted">Общие затраты</p>
                        </div>
                        <div class="col-md-4">
                            <h3 class="text-dark">{{ number_format($costBreakdown->count()) }}</h3>
                            <p class="text-muted">Используемых ингредиентов</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
