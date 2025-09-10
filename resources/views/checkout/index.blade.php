@extends('layouts.app')

@section('title', 'Оформление заказа')

@section('main_content')
<div class="container min-vh-100 d-flex flex-column" style="margin-top: 120px">
    <section id="checkout" class="my-5 py-5 flex-grow-1">
        <div class="row">
            <div class="col-md-8">
                <h2 class="mb-4">Оформление заказа</h2>
                
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form action="{{ route('checkout.process') }}" method="POST">
                    @csrf
                    
                    <!-- Способ оплаты -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Способ оплаты</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="payment_method" id="cash" value="cash" {{ old('payment_method') === 'cash' ? 'checked' : '' }}>
                                <label class="form-check-label" for="cash">
                                    <i class="iconify me-2" data-icon="mdi:cash"></i>
                                    Наличными при получении
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="card" value="card" {{ old('payment_method') === 'card' ? 'checked' : '' }}>
                                <label class="form-check-label" for="card">
                                    <i class="iconify me-2" data-icon="mdi:credit-card"></i>
                                    Картой при получении
                                </label>
                            </div>
                            @error('payment_method')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Способ получения -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Способ получения</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="delivery_method" id="pickup" value="pickup" {{ old('delivery_method') === 'pickup' ? 'checked' : '' }}>
                                <label class="form-check-label" for="pickup">
                                    <i class="iconify me-2" data-icon="mdi:store"></i>
                                    Самовывоз из кафе
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="delivery_method" id="delivery" value="delivery" {{ old('delivery_method') === 'delivery' ? 'checked' : '' }}>
                                <label class="form-check-label" for="delivery">
                                    <i class="iconify me-2" data-icon="mdi:truck-delivery"></i>
                                    Доставка
                                </label>
                            </div>
                            @error('delivery_method')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Адрес доставки -->
                    <div class="card mb-4" id="delivery-address-card" style="display: none;">
                        <div class="card-header">
                            <h5 class="mb-0">Адрес доставки</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="delivery_address" class="form-label">Адрес</label>
                                <input type="text" class="form-control @error('delivery_address') is-invalid @enderror" 
                                       id="delivery_address" name="delivery_address" value="{{ old('delivery_address') }}" 
                                       placeholder="Укажите адрес доставки">
                                @error('delivery_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Контактная информация -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Контактная информация</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Номер телефона</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone', Auth::user()->phone) }}" 
                                       placeholder="+7 (999) 999-99-99">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="notes" class="form-label">Комментарий к заказу (необязательно)</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="3" 
                                          placeholder="Дополнительные пожелания к заказу">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100">Оформить заказ</button>
                </form>
            </div>

            <!-- Сводка заказа -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Ваш заказ</h5>
                    </div>
                    <div class="card-body">
                        @if($cartItems && count($cartItems) > 0)
                            @foreach($cartItems as $id => $item)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <h6 class="mb-0">{{ $item['name'] }}</h6>
                                        <small class="text-muted">{{ $item['quantity'] }} шт. × {{ $item['price'] }} ₽</small>
                                    </div>
                                    <span class="fw-bold">{{ $item['price'] * $item['quantity'] }} ₽</span>
                                </div>
                            @endforeach
                            <hr>
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Итого:</h5>
                                <h5 class="mb-0 text-primary">{{ $total }} ₽</h5>
                            </div>
                        @else
                            <p class="text-center text-muted">Корзина пуста</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deliveryRadio = document.getElementById('delivery');
    const pickupRadio = document.getElementById('pickup');
    const addressCard = document.getElementById('delivery-address-card');
    const addressInput = document.getElementById('delivery_address');

    function toggleAddressCard() {
        if (deliveryRadio.checked) {
            addressCard.style.display = 'block';
            addressInput.required = true;
        } else {
            addressCard.style.display = 'none';
            addressInput.required = false;
        }
    }

    deliveryRadio.addEventListener('change', toggleAddressCard);
    pickupRadio.addEventListener('change', toggleAddressCard);

    // Проверяем при загрузке страницы
    toggleAddressCard();
});
</script>
@endsection
