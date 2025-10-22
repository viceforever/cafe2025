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

                <form action="{{ route('checkout.process') }}" method="POST" id="checkout-form">
                    @csrf
                    
                    {{-- Способ оплаты --}}
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

                    {{-- Способ получения --}}
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

                    {{-- Структурированный адрес доставки --}}
                    <div class="card mb-4" id="delivery-address-card" style="display: none;">
                        <div class="card-header">
                            <h5 class="mb-0">Адрес доставки</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="delivery_city" class="form-label">Город <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('delivery_city') is-invalid @enderror" 
                                           id="delivery_city" name="delivery_city" value="Иркутск" readonly
                                           style="background-color: #f8f9fa;">
                                    <small class="text-muted">Доставка осуществляется только по городу Иркутск</small>
                                    @error('delivery_city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="delivery_street" class="form-label">Улица/Микрорайон <span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <input type="text" class="form-control @error('delivery_street') is-invalid @enderror" 
                                               id="delivery_street" name="delivery_street" value="{{ old('delivery_street') }}" 
                                               placeholder="Например: ул. Ленина или мкр. Юбилейный" autocomplete="off">
                                        <div id="street-suggestions" class="position-absolute w-100 bg-white border border-top-0 rounded-bottom shadow-sm" 
                                             style="display: none; z-index: 1000; max-height: 200px; overflow-y: auto;"></div>
                                    </div>
                                    @error('delivery_street')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="delivery_house" class="form-label">Дом <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('delivery_house') is-invalid @enderror" 
                                           id="delivery_house" name="delivery_house" value="{{ old('delivery_house') }}" 
                                           placeholder="Например: 15, 7А, 12/1">
                                    @error('delivery_house')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="delivery_apartment" class="form-label">Квартира</label>
                                    <input type="text" class="form-control @error('delivery_apartment') is-invalid @enderror" 
                                           id="delivery_apartment" name="delivery_apartment" value="{{ old('delivery_apartment') }}" 
                                           placeholder="Например: 25">
                                    <small class="text-muted">Необязательно для частных домов</small>
                                    @error('delivery_apartment')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            {{-- Предварительный просмотр адреса --}}
                            <div class="mt-3 p-3 bg-light rounded" id="address-preview" style="display: none;">
                                <h6 class="mb-2">Предварительный адрес:</h6>
                                <div id="address-preview-text" class="text-muted"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Контактная информация --}}
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

                    <button type="submit" class="btn btn-primary btn-lg w-100" id="submit-btn">Оформить заказ</button>
                </form>
            </div>

            {{-- Сводка заказа --}}
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

{{-- Обновленный JavaScript для структурированной валидации адреса --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const deliveryRadio = document.getElementById('delivery');
    const pickupRadio = document.getElementById('pickup');
    const addressCard = document.getElementById('delivery-address-card');
    const streetInput = document.getElementById('delivery_street');
    const houseInput = document.getElementById('delivery_house');
    const apartmentInput = document.getElementById('delivery_apartment');
    const streetSuggestions = document.getElementById('street-suggestions');
    const addressPreview = document.getElementById('address-preview');
    const addressPreviewText = document.getElementById('address-preview-text');
    const form = document.getElementById('checkout-form');
    const submitBtn = document.getElementById('submit-btn');
    
    let debounceTimer;
    let addressValid = false;
    let streetValid = false;
    let validStreets = new Set();
    let lastSearchQuery = '';

    function toggleAddressCard() {
        if (deliveryRadio && deliveryRadio.checked) {
            addressCard.style.display = 'block';
            streetInput.required = true;
            houseInput.required = true;
            validateAddress();
        } else {
            addressCard.style.display = 'none';
            streetInput.required = false;
            houseInput.required = false;
            streetInput.value = '';
            houseInput.value = '';
            apartmentInput.value = '';
            streetSuggestions.style.display = 'none';
            addressPreview.style.display = 'none';
            streetValid = true;
            addressValid = true;
            validStreets.clear();
            updateSubmitButton();
        }
    }

    function validateAddress() {
        const street = streetInput.value.trim();
        const house = houseInput.value.trim();
        
        if (deliveryRadio.checked) {
            streetValid = validStreets.has(street) || street.length === 0;
            addressValid = streetValid && street.length >= 3 && house.length >= 1;
            updateAddressPreview();
            updateSubmitButton();
        }
    }

    function updateAddressPreview() {
        const street = streetInput.value.trim();
        const house = houseInput.value.trim();
        const apartment = apartmentInput.value.trim();
        
        if (street && house) {
            let addressParts = ['г. Иркутск', street, 'д. ' + house];
            if (apartment) {
                addressParts.push('кв. ' + apartment);
            }
            
            addressPreviewText.textContent = addressParts.join(', ');
            addressPreview.style.display = 'block';
        } else {
            addressPreview.style.display = 'none';
        }
    }

    function updateSubmitButton() {
        if (deliveryRadio.checked && !addressValid) {
            submitBtn.disabled = true;
            if (!streetValid && streetInput.value.trim().length >= 3) {
                submitBtn.textContent = 'Улица не найдена в базе адресов';
            } else {
                submitBtn.textContent = 'Заполните корректный адрес';
            }
            submitBtn.classList.remove('btn-primary');
            submitBtn.classList.add('btn-secondary');
        } else {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Оформить заказ';
            submitBtn.classList.remove('btn-secondary');
            submitBtn.classList.add('btn-primary');
        }
    }

    function fetchStreetSuggestions(query) {
        if (query.length < 3) {
            streetSuggestions.style.display = 'none';
            validStreets.clear();
            validateAddress();
            return;
        }

        lastSearchQuery = query;
        const fullQuery = `г Иркутск ${query}`;
        const url = `/api/address-suggestions?query=${encodeURIComponent(fullQuery)}`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (lastSearchQuery !== query) {
                    return;
                }
                
                streetSuggestions.innerHTML = '';
                validStreets.clear();
                
                if (data.error) {
                    console.error('API Error:', data.error);
                    const errorItem = document.createElement('div');
                    errorItem.className = 'p-2 text-danger small';
                    errorItem.textContent = data.error;
                    streetSuggestions.appendChild(errorItem);
                    streetSuggestions.style.display = 'block';
                    validateAddress();
                    return;
                }
                
                if (data.length > 0) {
                    const uniqueStreets = new Set();
                    const uniqueSuggestions = [];
                    
                    data.forEach(suggestion => {
                        const addressParts = suggestion.value.split(', ');
                        let streetName = '';
                        
                        for (let part of addressParts) {
                            if (part.includes('ул ') || part.includes('пр-кт ') || 
                                part.includes('мкр ') || part.includes('пер ') ||
                                part.includes('наб ') || part.includes('б-р ')) {
                                streetName = part;
                                break;
                            }
                        }
                        
                        if (streetName && !uniqueStreets.has(streetName)) {
                            uniqueStreets.add(streetName);
                            uniqueSuggestions.push(streetName);
                            validStreets.add(streetName);
                        }
                    });
                    
                    uniqueSuggestions.forEach(streetName => {
                        const suggestionItem = document.createElement('div');
                        suggestionItem.className = 'p-2 border-bottom cursor-pointer suggestion-item';
                        suggestionItem.style.cursor = 'pointer';
                        suggestionItem.textContent = streetName;
                        
                        suggestionItem.addEventListener('click', function() {
                            streetInput.value = streetName;
                            streetSuggestions.style.display = 'none';
                            validateAddress();
                        });
                        
                        suggestionItem.addEventListener('mouseenter', function() {
                            this.style.backgroundColor = '#f8f9fa';
                        });
                        
                        suggestionItem.addEventListener('mouseleave', function() {
                            this.style.backgroundColor = 'white';
                        });
                        
                        streetSuggestions.appendChild(suggestionItem);
                    });
                    
                    if (streetSuggestions.children.length > 0) {
                        streetSuggestions.style.display = 'block';
                    } else {
                        streetSuggestions.style.display = 'none';
                    }
                } else {
                    const noResultsItem = document.createElement('div');
                    noResultsItem.className = 'p-2 text-muted';
                    noResultsItem.textContent = 'Улица не найдена';
                    streetSuggestions.appendChild(noResultsItem);
                    streetSuggestions.style.display = 'block';
                }
                
                validateAddress();
            })
            .catch(error => {
                console.error('Ошибка получения подсказок:', error);
                streetSuggestions.style.display = 'none';
                validStreets.clear();
                validateAddress();
            });
    }

    streetInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            fetchStreetSuggestions(query);
        }, 300);
        
        validateAddress();
    });

    houseInput.addEventListener('input', validateAddress);
    apartmentInput.addEventListener('input', function() {
        updateAddressPreview();
    });

    document.addEventListener('click', function(event) {
        if (!streetInput.contains(event.target) && !streetSuggestions.contains(event.target)) {
            streetSuggestions.style.display = 'none';
        }
    });

    if (deliveryRadio) {
        deliveryRadio.addEventListener('change', toggleAddressCard);
    }
    if (pickupRadio) {
        pickupRadio.addEventListener('change', toggleAddressCard);
    }

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

        if (deliveryMethod.value === 'delivery') {
            if (!streetValid && streetInput.value.trim().length >= 3) {
                e.preventDefault();
                alert('Указанная улица не найдена в базе адресов Иркутска. Пожалуйста, выберите улицу из предложенных вариантов.');
                streetInput.focus();
                return false;
            }
            
            if (!addressValid) {
                e.preventDefault();
                alert('Пожалуйста, заполните корректный адрес доставки');
                streetInput.focus();
                return false;
            }
        }
    });

    toggleAddressCard();
});
</script>
@endsection
