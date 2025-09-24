@extends('layouts.app')

@section('content')
<div class="container" style="padding-top: 220px;">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Отчет по продажам</h4>
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
                                <label for="category_id">Категория:</label>
                                <select name="category_id" id="category_id" class="form-control">
                                    <option value="">Все категории</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" 
                                                {{ $categoryId == $category->id ? 'selected' : '' }}>
                                            {{ $category->name_category }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="product_id">Товар:</label>
                                <select name="product_id" id="product_id" class="form-control">
                                    <option value="">Все товары</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" 
                                                {{ $productId == $product->id ? 'selected' : '' }}>
                                            {{ $product->name_product }}
                                        </option>
                                    @endforeach
                                </select>
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
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-download"></i> Экспорт
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" 
                                           href="{{ route('admin.reports.sales.export', ['format' => 'pdf']) }}?{{ http_build_query(request()->all()) }}">
                                            <i class="fas fa-file-pdf"></i> PDF
                                        </a>
                                        <a class="dropdown-item" 
                                           href="{{ route('admin.reports.sales.export', ['format' => 'excel']) }}?{{ http_build_query(request()->all()) }}">
                                            <i class="fas fa-file-excel"></i> Excel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Статистика -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5>Общая выручка</h5>
                                    <h3>{{ number_format($totalRevenue, 2) }} ₽</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5>Количество заказов</h5>
                                    <h3>{{ $totalOrders }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5>Средний чек</h5>
                                    <h3>{{ number_format($averageOrderValue, 2) }} ₽</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Статистика по товарам -->
                    @if(count($productStats) > 0)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Статистика по товарам</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Товар</th>
                                            <th>Продано</th>
                                            <th>Выручка</th>
                                            <th>Заказов</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($productStats as $stat)
                                        <tr>
                                            <td>{{ $stat->name_product }}</td>
                                            <td>{{ $stat->total_quantity }}</td>
                                            <td>{{ number_format($stat->total_revenue, 2) }} ₽</td>
                                            <td>{{ $stat->orders_count }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Список заказов -->
                    <div class="card">
                        <div class="card-header">
                            <h5>Заказы ({{ $orders->count() }})</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Дата</th>
                                            <th>Клиент</th>
                                            <th>Статус</th>
                                            <th>Сумма</th>
                                            <th>Товары</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($orders as $order)
                                        <tr>
                                            <td>{{ $order->id }}</td>
                                            <td>{{ $order->created_at->format('d.m.Y H:i') }}</td>
                                            <td>{{ $order->user ? $order->user->first_name . ' ' . $order->user->last_name : 'Гость' }}</td>
                                            <td>
                                                <span class="badge 
                                                    @if($order->status === 'В обработке') text-bg-warning
                                                    @elseif($order->status === 'Подтвержден') text-bg-success
                                                    @elseif($order->status === 'Готовится') text-bg-info
                                                    @elseif($order->status === 'Готов к выдаче') text-bg-primary
                                                    @elseif($order->status === 'Выдан') text-bg-success
                                                    @elseif($order->status === 'Отменен') text-bg-danger
                                                    @else text-bg-secondary
                                                    @endif
                                                    " style="
                                                    @if($order->status === 'В обработке') 
                                                        background-color: #fd7e14 !important; color: white !important;
                                                    @elseif($order->status === 'Подтвержден') 
                                                        background-color: #198754 !important; color: white !important;
                                                    @elseif($order->status === 'Готовится') 
                                                        background-color: #0dcaf0 !important; color: #000 !important;
                                                    @elseif($order->status === 'Готов к выдаче') 
                                                        background-color: #0d6efd !important; color: white !important;
                                                    @elseif($order->status === 'Выдан') 
                                                        background-color: #198754 !important; color: white !important;
                                                    @elseif($order->status === 'Отменен') 
                                                        background-color: #dc3545 !important; color: white !important;
                                                    @else 
                                                        background-color: #6c757d !important; color: white !important;
                                                    @endif
                                                    font-weight: 600; padding: 8px 12px; border-radius: 6px;">
                                                    {{ $order->status }}
                                                </span>
                                            </td>
                                            <td>{{ number_format($order->total_amount, 2) }} ₽</td>
                                            <td>
                                                @foreach($order->orderItems as $item)
                                                    {{ $item->product->name_product }} ({{ $item->quantity }})
                                                    @if(!$loop->last), @endif
                                                @endforeach
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center">Заказы не найдены</td>
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
