@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Аналитика товаров</h2>
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
                <!-- Популярность товаров -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Популярность товаров (по количеству)</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Место</th>
                                            <th>Товар</th>
                                            <th>Продано</th>
                                            <th>Заказов</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($popularProducts as $index => $item)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-{{ $index < 3 ? ['warning', 'secondary', 'dark'][$index] : 'light text-dark' }}">
                                                        {{ $index + 1 }}
                                                    </span>
                                                </td>
                                                <td>{{ $item->product->name_product }}</td>
                                                <td>{{ $item->total_quantity }}</td>
                                                <td>{{ $item->order_count }}</td>
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

                <!-- Выручка по товарам -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Выручка по товарам</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Товар</th>
                                            <th>Выручка</th>
                                            <th>Продано</th>
                                            <th>Средняя цена</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($revenueByProduct->take(20) as $item)
                                            <tr>
                                                <td>{{ $item->product->name_product }}</td>
                                                <td>{{ number_format($item->total_revenue, 0) }} ₽</td>
                                                <td>{{ $item->total_quantity }}</td>
                                                <td>{{ number_format($item->total_revenue / $item->total_quantity, 0) }} ₽</td>
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
            </div>
        </div>
    </div>
</div>
@endsection
