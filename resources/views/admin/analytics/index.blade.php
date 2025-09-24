@extends('layouts.app')

@section('content')
<div class="container" style="padding-top: 220px;">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Аналитика и статистика</h2>
                <!-- увеличил отступы между кнопками с gap-2 до gap-3 -->
                <div class="d-flex gap-3" role="group">
                    <a href="{{ route('admin.analytics.products') }}" class="btn btn-outline-primary">Товары</a>
                    <a href="{{ route('admin.analytics.ingredients') }}" class="btn btn-outline-success">Ингредиенты</a>
                    <a href="{{ route('admin.analytics.financial') }}" class="btn btn-outline-info">Финансы</a>
                </div>
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

            <!-- Основные метрики -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4>{{ number_format($totalRevenue, 0) }} ₽</h4>
                                    <p class="mb-0">Выручка</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="iconify" data-icon="mdi:cash" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <!-- восстановил оригинальный цвет заказов с bg-dark на bg-success -->
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4>{{ $totalOrders }}</h4>
                                    <p class="mb-0">Заказов</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="iconify" data-icon="mdi:receipt" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <!-- восстановил оригинальный цвет среднего чека с bg-secondary на bg-info -->
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4>{{ number_format($averageOrderValue, 0) }} ₽</h4>
                                    <p class="mb-0">Средний чек</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="iconify" data-icon="mdi:calculator" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <!-- восстановил оригинальное условие для прибыли с bg-dark на bg-success -->
                    <div class="card bg-{{ $profit >= 0 ? 'success' : 'danger' }} text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4>{{ number_format($profit, 0) }} ₽</h4>
                                    <p class="mb-0">Прибыль</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="iconify" data-icon="mdi:trending-{{ $profit >= 0 ? 'up' : 'down' }}" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Популярные товары -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Популярные товары</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Товар</th>
                                            <th>Продано</th>
                                            <th>Заказов</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($popularProducts as $item)
                                            <tr>
                                                <td>{{ $item->product->name_product }}</td>
                                                <td>{{ $item->total_quantity }}</td>
                                                <td>{{ $item->order_count }}</td>
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

                <!-- Статистика по оплате -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Способы оплаты</h5>
                        </div>
                        <div class="card-body">
                            @forelse($paymentStats as $stat)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>{{ $stat->payment_method === 'cash' ? 'Наличными' : 'Картой' }}</span>
                                    <div>
                                        <span class="badge bg-primary">{{ $stat->count }} заказов</span>
                                        <span class="fw-bold">{{ number_format($stat->total, 0) }} ₽</span>
                                    </div>
                                </div>
                                <div class="progress mb-3" style="height: 8px;">
                                    <div class="progress-bar" style="width: {{ ($stat->total / $totalRevenue) * 100 }}%"></div>
                                </div>
                            @empty
                                <p class="text-center text-muted">Нет данных</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Статистика по доставке -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Способы получения</h5>
                        </div>
                        <div class="card-body">
                            @forelse($deliveryStats as $stat)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>{{ $stat->delivery_method === 'pickup' ? 'Самовывоз' : 'Доставка' }}</span>
                                    <div>
                                        <!-- восстановил оригинальный цвет способов получения с bg-dark на bg-success -->
                                        <span class="badge bg-success">{{ $stat->count }} заказов</span>
                                        <span class="fw-bold">{{ number_format($stat->total, 0) }} ₽</span>
                                    </div>
                                </div>
                                <div class="progress mb-3" style="height: 8px;">
                                    <!-- восстановил оригинальный цвет прогресс-бара с bg-dark на bg-success -->
                                    <div class="progress-bar bg-success" style="width: {{ ($stat->total / $totalRevenue) * 100 }}%"></div>
                                </div>
                            @empty
                                <p class="text-center text-muted">Нет данных</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Статусы заказов -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Статусы заказов</h5>
                        </div>
                        <div class="card-body">
                            @forelse($orderStatusStats as $stat)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>{{ $stat->status }}</span>
                                    <span class="badge bg-secondary">{{ $stat->count }}</span>
                                </div>
                            @empty
                                <p class="text-center text-muted">Нет данных</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- График выручки по дням -->
            @if($dailyRevenue->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h5>Выручка по дням</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Дата</th>
                                        <th>Заказов</th>
                                        <th>Выручка</th>
                                        <th>Средний чек</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dailyRevenue as $day)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($day->date)->format('d.m.Y') }}</td>
                                            <td>{{ $day->orders }}</td>
                                            <td>{{ number_format($day->revenue, 0) }} ₽</td>
                                            <td>{{ number_format($day->orders > 0 ? $day->revenue / $day->orders : 0, 0) }} ₽</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
