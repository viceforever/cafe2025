@extends('layouts.app')

@section('title', 'Заказ оформлен')

@section('main_content')
<div class="container min-vh-100 d-flex flex-column justify-content-center" style="margin-top: 120px">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="iconify text-success" data-icon="mdi:check-circle" style="font-size: 4rem;"></i>
                    </div>
                    
                    <h2 class="mb-3">Заказ успешно оформлен!</h2>
                    <p class="text-muted mb-4">Номер вашего заказа: <strong>#{{ $order->id }}</strong></p>
                    
                    <div class="row mb-4">
                        <div class="col-md-7">
                            <div class="card bg-light">
                                <div class="card-body text-start">
                                    <h6 class="card-title">Детали заказа</h6>
                                    <p class="mb-1"><strong>Сумма:</strong> {{ $order->total_amount }} ₽</p>
                                    <p class="mb-1"><strong>Оплата:</strong> {{ $order->payment_method_text }}</p>
                                    <p class="mb-1"><strong>Получение:</strong> {{ $order->delivery_method_text }}</p>
                                    @if($order->delivery_method === 'delivery')
                                        <p class="mb-1" style="word-break: break-word;"><strong>Адрес:</strong> {{ $order->delivery_address }}</p>
                                    @endif
                                    <p class="mb-0"><strong>Телефон:</strong> {{ $order->phone }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="card bg-light">
                                <div class="card-body text-start">
                                    <h6 class="card-title">Состав заказа</h6>
                                    @foreach($order->orderItems as $item)
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>{{ $item->product->name_product }} × {{ $item->quantity }}</span>
                                            <span>{{ $item->price * $item->quantity }} ₽</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($order->notes)
                        <div class="alert alert-info">
                            <strong>Комментарий:</strong> {{ $order->notes }}
                        </div>
                    @endif
                    
                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ route('home') }}" class="btn btn-primary">Продолжить покупки</a>
                        <a href="{{ route('profile.show') }}" class="btn btn-outline-primary">Мои заказы</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
