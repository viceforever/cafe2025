@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4">Управление заказами</h2>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>№ заказа</th>
                                    <th>Клиент</th>
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
                                                    @foreach(['В обработке', 'Готовится', 'Готов к выдаче', 'Выдан', 'Отменен'] as $status)
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
                                        <td colspan="8" class="text-center">Заказы не найдены</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
