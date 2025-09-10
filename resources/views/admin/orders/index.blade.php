@extends('layouts.app')
@section('title', 'Управление заказами')
@section('main_content')
<div class="container min-vh-100 d-flex flex-column">
    <div class="row flex-grow-1" style="margin-top: 220px; margin-bottom: 50px;">
        <div class="col-12">
            <h1>Управление заказами</h1>

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Клиент</th>
                            <th>Сумма</th>
                            <th>Статус</th>
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
                                <td>{{ $order->total_amount }} ₽</td>
                                <td>{{ $order->status }}</td>
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
                                    <form action="{{ route('admin.orders.confirm', $order) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Подтвердить заказ?')">Подтвердить</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection