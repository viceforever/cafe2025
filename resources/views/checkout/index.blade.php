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
                            <div class="mb-3 position-relative">
                                <label for="delivery_address" class="form-label">Адрес</label>
                                <input type="text" class="form-control @error('delivery_address') is-invalid @enderror" 
                                       id="delivery_address" name="delivery_address" value="{{ old('delivery_address') }}" 
                                       placeholder="Начните вводить адрес..." autocomplete="off">
                                <div id="address-suggestions" class="position-absolute w-100 bg-white border border-top-0 rounded-bottom shadow-sm" style="display: none; z-index: 1000; max-height: 300px; overflow-y: auto;"></div>
                                @error('delivery_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    <i class="iconify me-1" data-icon="mdi:information-outline"></i>
                                    Начните вводить адрес, и мы предложим варианты
                                </small>
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
    const suggestionsContainer = document.getElementById('address-suggestions');

    let debounceTimer;
    let currentSuggestions = [];
    let selectedIndex = -1;

    console.log('[v0] DaData autocomplete initialized');
    console.log('[v0] CSRF token:', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'));

    function toggleAddressCard() {
        if (deliveryRadio.checked) {
            addressCard.style.display = 'block';
            addressInput.required = true;
        } else {
            addressCard.style.display = 'none';
            addressInput.required = false;
            addressInput.value = '';
            hideSuggestions();
        }
    }

    function showSuggestions() {
        suggestionsContainer.style.display = 'block';
    }

    function hideSuggestions() {
        suggestionsContainer.style.display = 'none';
        selectedIndex = -1;
    }

    function renderSuggestions(suggestions) {
        console.log('[v0] Rendering suggestions:', suggestions.length);
        
        if (suggestions.length === 0) {
            hideSuggestions();
            return;
        }

        currentSuggestions = suggestions;
        let html = '';
        
        suggestions.forEach((suggestion, index) => {
            html += `
                <div class="suggestion-item p-2 border-bottom cursor-pointer" data-index="${index}" style="cursor: pointer;">
                    <div class="fw-medium">${suggestion.value}</div>
                    ${suggestion.data.postal_code ? `<small class="text-muted">${suggestion.data.postal_code}</small>` : ''}
                </div>
            `;
        });

        suggestionsContainer.innerHTML = html;
        showSuggestions();

        // Добавляем обработчики кликов на подсказки
        suggestionsContainer.querySelectorAll('.suggestion-item').forEach(item => {
            item.addEventListener('click', function() {
                const index = parseInt(this.dataset.index);
                const suggestion = currentSuggestions[index];
                addressInput.value = suggestion.value;
                hideSuggestions();
                console.log('[v0] Address selected:', suggestion.value);
            });

            // Добавляем эффект hover
            item.addEventListener('mouseenter', function() {
                // Убираем выделение с других элементов
                suggestionsContainer.querySelectorAll('.suggestion-item').forEach(el => {
                    el.style.backgroundColor = '';
                });
                this.style.backgroundColor = '#f8f9fa';
                selectedIndex = parseInt(this.dataset.index);
            });
            
            item.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
            });
        });
    }

    async function fetchAddressSuggestions(query) {
        console.log('[v0] Fetching suggestions for:', query);
        
        if (query.length < 3) {
            console.log('[v0] Query too short, hiding suggestions');
            hideSuggestions();
            return;
        }

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            if (!csrfToken) {
                console.error('[v0] CSRF token not found');
                return;
            }

            console.log('[v0] Making request to /api/address/suggest');
            
            const response = await fetch('/api/address/suggest', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    query: query,
                    count: 8
                })
            });

            console.log('[v0] Response status:', response.status);

            if (response.ok) {
                const data = await response.json();
                console.log('[v0] Response data:', data);
                
                if (data.success && data.suggestions) {
                    renderSuggestions(data.suggestions);
                } else {
                    console.log('[v0] No suggestions in response');
                    hideSuggestions();
                }
            } else {
                const errorText = await response.text();
                console.error('[v0] API error:', response.status, errorText);
                hideSuggestions();
            }
        } catch (error) {
            console.error('[v0] Request error:', error);
            hideSuggestions();
        }
    }

    addressInput.addEventListener('input', function() {
        const query = this.value.trim();
        console.log('[v0] Input changed:', query);
        
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            fetchAddressSuggestions(query);
        }, 300);
    });

    // Скрываем подсказки при клике вне поля
    document.addEventListener('click', function(e) {
        if (!addressInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
            hideSuggestions();
        }
    });

    addressInput.addEventListener('keydown', function(e) {
        const suggestions = suggestionsContainer.querySelectorAll('.suggestion-item');
        
        if (suggestions.length === 0) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedIndex = selectedIndex < suggestions.length - 1 ? selectedIndex + 1 : 0;
            updateSelection(suggestions);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedIndex = selectedIndex > 0 ? selectedIndex - 1 : suggestions.length - 1;
            updateSelection(suggestions);
        } else if (e.key === 'Enter' && selectedIndex >= 0) {
            e.preventDefault();
            const suggestion = currentSuggestions[selectedIndex];
            addressInput.value = suggestion.value;
            hideSuggestions();
            console.log('[v0] Address selected via keyboard:', suggestion.value);
        } else if (e.key === 'Escape') {
            hideSuggestions();
        }
    });

    function updateSelection(suggestions) {
        suggestions.forEach((item, index) => {
            if (index === selectedIndex) {
                item.style.backgroundColor = '#f8f9fa';
            } else {
                item.style.backgroundColor = '';
            }
        });
    }

    deliveryRadio.addEventListener('change', toggleAddressCard);
    pickupRadio.addEventListener('change', toggleAddressCard);

    // Проверяем при загрузке страницы
    toggleAddressCard();

    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
        const deliveryMethod = document.querySelector('input[name="delivery_method"]:checked');
        const phone = document.getElementById('phone');

        if (!paymentMethod) {
            e.preventDefault();
            alert('Пожалуйста, выберите способ оплаты');
            return false;
        }

        if (!deliveryMethod) {
            e.preventDefault();
            alert('Пожалуйста, выберите способ получения');
            return false;
        }

        if (!phone.value.trim()) {
            e.preventDefault();
            alert('Пожалуйста, укажите номер телефона');
            phone.focus();
            return false;
        }

        if (deliveryMethod.value === 'delivery' && !addressInput.value.trim()) {
            e.preventDefault();
            alert('Пожалуйста, укажите адрес доставки');
            addressInput.focus();
            return false;
        }
    });
});
</script>
@endsection
