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
                    <ul class="nav nav-tabs mb-3" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">
                                Личные данные
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab">
                                Смена пароля
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="profileTabsContent">
                        <div class="tab-pane fade show active" id="profile" role="tabpanel">
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
                        </div>

                        <div class="tab-pane fade" id="password" role="tabpanel">
                            <form action="{{ route('profile.change-password') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Текущий пароль</label>
                                    <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                           id="current_password" name="current_password" required>
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Новый пароль</label>
                                    <input type="password" class="form-control @error('new_password') is-invalid @enderror" 
                                           id="new_password" name="new_password" minlength="8" required>
                                    @error('new_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Пароль должен содержать минимум 8 символов</div>
                                </div>
                                <div class="mb-3">
                                    <label for="new_password_confirmation" class="form-label">Подтвердите новый пароль</label>
                                    <input type="password" class="form-control" id="new_password_confirmation" 
                                           name="new_password_confirmation" minlength="8" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Изменить пароль</button>
                            </form>
                        </div>
                    </div>

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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('new_password_confirmation');
    
    function validatePasswords() {
        if (newPassword.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Пароли не совпадают');
        } else {
            confirmPassword.setCustomValidity('');
        }
    }
    
    newPassword.addEventListener('input', validatePasswords);
    confirmPassword.addEventListener('input', validatePasswords);
});
</script>
@endsection
