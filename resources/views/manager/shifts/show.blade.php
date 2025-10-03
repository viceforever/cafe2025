@extends('layouts.app')

@section('content')
<div class="container" style="padding-top: 220px;">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Отчет по смене #{{ $shift->id }}</h2>
                <a href="{{ route('manager.shifts.index') }}" class="btn btn-secondary">Назад к списку</a>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Информация о смене</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Дата:</strong> {{ date('d.m.Y', strtotime($shift->start_time)) }}</p>
                            <p><strong>Время начала:</strong> {{ date('H:i', strtotime($shift->start_time)) }}</p>
                            <p><strong>Время окончания:</strong> 
                                @if($shift->end_time)
                                    {{ date('H:i', strtotime($shift->end_time)) }}
                                @else
                                    В процессе
                                @endif
                            </p>
                            <p><strong>Длительность:</strong> {{ $shift->duration ?? 'В процессе' }}</p>
                            
                            <p><strong>Статус:</strong> 
                                <span class="badge bg-{{ $shift->isActive() ? 'success' : 'secondary' }}">
                                    {{ $shift->isActive() ? 'Активна' : 'Завершена' }}
                                </span>
                            </p>
                            @if($shift->notes)
                                <p><strong>Комментарии:</strong></p>
                                <div class="alert alert-info">{{ $shift->notes }}</div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Статистика</h5>
                        </div>
                        <div class="card-body">
                            {{-- Используем динамически вычисленную статистику из контроллера --}}
                            <p><strong>Всего заказов:</strong> {{ $stats['total_orders'] }}</p>
                            <p><strong>Общая выручка:</strong> {{ number_format($stats['total_revenue'], 2) }} ₽</p>
                            <p><strong>Наличными:</strong> {{ number_format($stats['cash_sales'], 2) }} ₽</p>
                            <p><strong>Картой:</strong> {{ number_format($stats['card_sales'], 2) }} ₽</p>
                            @if($stats['total_orders'] > 0)
                                <p><strong>Средний чек:</strong> {{ number_format($stats['total_revenue'] / $stats['total_orders'], 2) }} ₽</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            

            @if($orders->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h5>Заказы за смену</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
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
                                    @foreach($orders as $order)
                                        <tr>
                                            <td>#{{ $order->id }}</td>
                                            <td>{{ date('H:i', strtotime($order->created_at)) }}</td>
                                            <td>{{ $order->total_amount }} ₽</td>
                                            <td>{{ $order->payment_method_text }}</td>
                                            <td>
                                                <span class="badge {{ $order->status_badge_class }}">
                                                    {{ $order->status }}
                                                </span>
                                            </td>
                                            <td>
                                                @foreach($order->orderItems as $item)
                                                    {{ $item->product->name_product }} ({{ $item->quantity }}){{ !$loop->last ? ', ' : '' }}
                                                @endforeach
                                            </td>
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
