@extends('layouts.app')

@section('content')
<div class="container" style="padding-top: 220px;">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Отчет по смене #{{ $shift->id }}</h2>
                <a href="{{ route('admin.shifts.index') }}" class="btn btn-secondary">
                    Назад к списку
                </a>
            </div>

            <div class="row">
                {{-- Информация о смене --}}
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Информация о смене</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Сотрудник:</strong> {{ $shift->user->last_name }} {{ $shift->user->first_name }}
                            </div>
                            <div class="mb-3">
                                <strong>Телефон:</strong> {{ $shift->user->phone }}
                            </div>
                            <div class="mb-3">
                                <strong>Дата:</strong> {{ date('d.m.Y', strtotime($shift->start_time)) }}
                            </div>
                            <div class="mb-3">
                                <strong>Время начала:</strong> {{ date('H:i', strtotime($shift->start_time)) }}
                            </div>
                            <div class="mb-3">
                                <strong>Время окончания:</strong> 
                                @if($shift->end_time)
                                    {{ date('H:i', strtotime($shift->end_time)) }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                            <div class="mb-3">
                                <strong>Длительность:</strong> 
                                @if($shift->start_time && $shift->end_time)
                                    {{ $shift->duration }}
                                @else
                                    <span class="badge bg-success">В процессе</span>
                                @endif
                            </div>
                            <div class="mb-3">
                                <strong>Статус:</strong>
                                <span class="badge bg-{{ $shift->isActive() ? 'success' : 'secondary' }}">
                                    {{ $shift->isActive() ? 'Активна' : 'Завершена' }}
                                </span>
                            </div>
                            @if($shift->notes)
                                <div class="mb-3">
                                    <strong>Комментарий завершения:</strong>
                                    <p class="mb-0 mt-2 p-3 bg-light rounded">{{ $shift->notes }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Статистика --}}
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Статистика</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Всего заказов:</strong> {{ $stats['total_orders'] }}
                            </div>
                            <div class="mb-3">
                                <strong>Общая выручка:</strong> {{ number_format($stats['total_revenue'], 2) }} ₽
                            </div>
                            <div class="mb-3">
                                <strong>Наличными:</strong> {{ number_format($stats['cash_sales'], 2) }} ₽
                            </div>
                            <div class="mb-3">
                                <strong>Картой:</strong> {{ number_format($stats['card_sales'], 2) }} ₽
                            </div>
                            <div class="mb-3">
                                <strong>Средний чек:</strong> {{ number_format($stats['average_check'], 2) }} ₽
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Заказы за смену --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Заказы за смену</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>№</th>
                                    <th>Время</th>
                                    <th>Сумма</th>
                                    <th>Оплата</th>
                                    <th>Статус</th>
                                    <th>Состав</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($shift->orders as $order)
                                    <tr class="{{ $order->status === 'Отменен' ? 'table-danger' : '' }}">
                                        <td>#{{ $order->id }}</td>
                                        <td>{{ date('H:i', strtotime($order->created_at)) }}</td>
                                        <td>{{ number_format($order->total_amount, 2) }} ₽</td>
                                        <td>
                                            @if($order->payment_method === 'cash')
                                                Наличными
                                            @else
                                                Картой
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $order->status_badge_class }}">
                                                {{ $order->status }}
                                            </span>
                                        </td>
                                        <td>
                                            {{-- Используем orderItems вместо items --}}
                                            @if($order->orderItems && $order->orderItems->count() > 0)
                                                @foreach($order->orderItems as $item)
                                                    @if($item->product)
                                                        {{ $item->product->name_product }} ({{ $item->quantity }})@if(!$loop->last), @endif
                                                    @else
                                                        <span class="text-danger">[Продукт #{{ $item->product_id }} не найден]</span>
                                                    @endif
                                                @endforeach
                                            @else
                                                <span class="text-muted">({{ $order->orderItems->count() }})</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Заказов нет</td>
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
