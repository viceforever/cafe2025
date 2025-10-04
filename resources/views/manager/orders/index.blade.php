@extends('layouts.app')
@section('title', 'Управление заказами')
@section('main_content')
<div class="container min-vh-100 d-flex flex-column">
    <div class="row flex-grow-1" style="margin-top: 220px; margin-bottom: 50px;">
        <div class="col-12">
            <h1>Управление заказами</h1>

            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('manager.orders') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="status" class="form-label">Фильтр по статусу:</label>
                            <select name="status" id="status" class="form-select">
                                <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>
                                    Все статусы
                                </option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                                        {{ $status }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">Применить</button>
                        </div>
                        @if(request('status'))
                            <div class="col-md-2 d-flex align-items-end">
                                <a href="{{ route('manager.orders') }}" class="btn btn-outline-secondary">Сбросить</a>
                            </div>
                        @endif
                    </form>
                </div>
            </div>

            {{-- Updated styles to match admin version for proper dropdown positioning --}}
            <style>
                .table-responsive {
                    overflow: visible !important;
                }
                .dropdown-menu {
                    z-index: 1050 !important;
                }
                /* Changed positioning to show dropdown above button without overlapping */
                .dropup .dropdown-menu {
                    bottom: 0 !important;
                }
            </style>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>№ заказа</th>
                            <th>Клиент</th>
                            <th>Сумма</th>
                            <th>Статус</th>
                            <th>Способ оплаты</th>
                            <th>Способ получения</th>
                            <th>Комментарий</th>
                            <th>Дата создания</th>
                            <th>Состав заказа</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td>{{ $order->id }}</td>
                                <td>{{ $order->user->last_name }} {{ $order->user->first_name }}</td>
                                <td style="white-space: nowrap;">{{ $order->total_amount }} ₽</td>
                                <td>
                                    {{-- Applied admin status styling with improved colors and contrast --}}
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
                                <td>{{ $order->payment_method_text }}</td>
                                <td>{{ $order->delivery_method_text }}</td>
                                <td>
                                    @if($order->notes)
                                        <span class="text-muted" title="{{ $order->notes }}">
                                            {{ Str::limit($order->notes, 30) }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $order->created_at->format('d.m.Y H:i') }}</td>
                                <td>
                                    @if($order->orderItems && $order->orderItems->count() > 0)
                                        <ul class="list-unstyled">
                                            @foreach ($order->orderItems as $item)
                                                <li>{{ $item->product->name_product }} - {{ $item->quantity }} шт. ({{ $item->price }} ₽)</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p>Нет данных о составе заказа</p>
                                    @endif
                                </td>
                                <td>
                                    {{-- Updated dropdown to match admin version with proper attributes --}}
                                    <div class="dropdown dropup">
                                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" 
                                                type="button" data-bs-toggle="dropdown" data-bs-auto-close="true">
                                            Изменить статус
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            @foreach(['В обработке', 'Подтвержден', 'Готовится', 'Готов к выдаче', 'Выдан', 'Отменен'] as $status)
                                                @if($status !== $order->status)
                                                    <li>
                                                        <form action="{{ route('manager.orders.update-status', $order) }}" method="POST">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status" value="{{ $status }}">
                                                            <button type="submit" class="dropdown-item">{{ $status }}</button>
                                                        </form>
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($orders->hasPages())
                <div class="mt-4">
                    {{ $orders->appends(request()->query())->links('custom.pagination') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
