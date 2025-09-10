@extends('layouts.app')

@section('title', 'Профиль пользователя')

@section('main_content')
<div class="container min-vh-100 d-flex flex-column">
    <div class="row flex-grow-1" style="margin-top: 220px; margin-bottom: 50px;">
        <div class="col-12">
            <h1>Профиль пользователя</h1>
            
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="row">
                <div class="col-md-6">
                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="first_name" class="form-label">Имя</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" value="{{ $user->first_name }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Фамилия</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" value="{{ $user->last_name }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Номер телефона</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="{{ $user->phone }}" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Обновить данные</button>
                    </form>
                    @if($errors->any())
                    <div class="alert alert-danger mt-3">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                            <li>{{$error}}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
                
                <div class="col-md-6">
                    <h2>Мои заказы</h2>
                    @if($orders->count() > 0)
                        <ul class="list-group">
                            @foreach($orders as $order)
                                <li class="list-group-item">
                                    <strong>Заказ #{{ $order->id }}</strong><br>
                                    Дата: {{ $order->created_at->format('d.m.Y H:i') }}<br>
                                    Детали: <br>
                                    @foreach($order->orderItems as $item)
                                    {{ $item->product->name_product }} - {{ $item->quantity }} шт = {{ $item->price }} ₽<br>
                                    @endforeach
                                    <br>
                                    
                                    Статус: {{ $order->status }}<br>
                                    Сумма: {{ $order->total_amount }} ₽
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p>У вас пока нет заказов.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection