@extends('layouts.app')

@section('content')
<div class="container" style="padding-top: 220px;">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4">Управление заказами</h2>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <!-- Добавляем фильтрацию по статусу -->
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

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>№ заказа</th>
                                    <th>Клиент</th>
                                    <th>Состав заказа</th> <!-- добавил колонку состава заказа -->
                                    <th>Сумма</th>
                                    <th>Статус</th>
                                    <th>Оплата</th>
                                    <th>Получение</th>
                                    <th>Дата</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                    <tr>
                                        <td>#{{ $order->id }}</td>
                                        <td>{{ $order->user->first_name }} {{ $order->user->last_name }}</td>
                                        <!-- добавил отображение состава заказа -->
                                        <td>
                                            <small>
                                                @foreach($order->orderItems as $item)
                                                    {{ $item->product->name_product }} ({{ $item->quantity }} шт.){{ !$loop->last ? ', ' : '' }}
                                                @endforeach
                                            </small>
                                        </td>
                                        <td>{{ $order->total_amount }} ₽</td>
                                        <td>
                                            <span class="badge {{ $order->status_badge_class }}">
                                                {{ $order->status }}
                                            </span>
                                        </td>
                                        <td>{{ $order->payment_method_text }}</td>
                                        <td>{{ $order->delivery_method_text }}</td>
                                        <td>{{ $order->created_at->format('d.m.Y H:i') }}</td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" 
                                                        type="button" data-bs-toggle="dropdown">
                                                    Изменить статус
                                                </button>
                                                <ul class="dropdown-menu">
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
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center"> <!-- изменил colspan на 9 -->
                                            Заказы не найдены
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Пагинация -->
            @if($orders->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{-- заменил стандартную пагинацию на кастомную русскую --}}
                    {{ $orders->appends(request()->query())->links('custom.pagination') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
