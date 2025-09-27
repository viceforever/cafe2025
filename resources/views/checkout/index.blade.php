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

                    {{-- Адрес доставки --}}
                    <div class="card mb-4" id="delivery-address-card" style="display: none;">
                        <div class="card-header">
                            <h5 class="mb-0">Адрес доставки</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="delivery_address" class="form-label">Адрес</label>
                                {{-- обновляем поле ввода адреса с контейнером для автоподсказок --}}
                                <div class="position-relative">
                                    <input type="text" class="form-control @error('delivery_address') is-invalid @enderror" 
                                           id="delivery_address" name="delivery_address" value="{{ old('delivery_address') }}" 
                                           placeholder="Начните вводить адрес..." autocomplete="off">
                                    <div id="address-suggestions" class="position-absolute w-100 bg-white border border-top-0 rounded-bottom shadow-sm" 
                                         style="display: none; z-index: 1000; max-height: 200px; overflow-y: auto;"></div>
                                </div>
                                @error('delivery_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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

                    <button type="submit" class="btn btn-primary btn-lg w-100">Оформить заказ</button>
                    
                    {{-- Добавляем отладочные кнопки для тестирования DaData --}}
                    <div class="mt-4 p-3 bg-light rounded">
                        <h6>Отладка DaData:</h6>
                        {{-- Добавляю кнопку для базового API теста --}}
                        <button type="button" class="btn btn-sm btn-outline-primary me-2" onclick="testBasicApi()">
                            Базовый API тест
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary me-2" onclick="testSimple()">
                            Простой тест
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-info me-2" onclick="testDaDataConfig()">
                            Проверить конфигурацию DaData
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="testDaDataApi()">
                            Тестировать API DaData
                        </button>
                        <div id="debug-results" class="mt-3" style="display: none;">
                            <pre id="debug-output" class="bg-white p-2 border rounded small"></pre>
                        </div>
                    </div>
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

{{-- добавляем JavaScript для работы с DaData подсказками --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const deliveryRadio = document.getElementById('delivery');
    const pickupRadio = document.getElementById('pickup');
    const addressCard = document.getElementById('delivery-address-card');
    const addressInput = document.getElementById('delivery_address');
    const suggestionsContainer = document.getElementById('address-suggestions');
    let debounceTimer;

    function toggleAddressCard() {
        if (deliveryRadio && deliveryRadio.checked) {
            addressCard.style.display = 'block';
            addressInput.required = true;
        } else {
            addressCard.style.display = 'none';
            addressInput.required = false;
            addressInput.value = '';
            suggestionsContainer.style.display = 'none';
        }
    }

    // Получить подсказки адресов от DaData API
    function fetchAddressSuggestions(query) {
        console.log('[v0] Запрос подсказок для:', query);
        
        if (query.length < 3) {
            suggestionsContainer.style.display = 'none';
            return;
        }

        const url = `/api/address-suggestions?query=${encodeURIComponent(query)}`;
        console.log('[v0] URL запроса:', url);

        fetch(url)
            .then(response => {
                console.log('[v0] Статус ответа:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('[v0] Данные ответа:', data);
                suggestionsContainer.innerHTML = '';
                
                if (data.error) {
                    console.error('[v0] Ошибка API:', data.error);
                    const errorItem = document.createElement('div');
                    errorItem.className = 'p-2 text-danger';
                    errorItem.textContent = 'Ошибка загрузки подсказок: ' + data.error;
                    suggestionsContainer.appendChild(errorItem);
                    suggestionsContainer.style.display = 'block';
                    return;
                }
                
                if (data.length > 0) {
                    console.log('[v0] Найдено подсказок:', data.length);
                    data.forEach(suggestion => {
                        const suggestionItem = document.createElement('div');
                        suggestionItem.className = 'p-2 border-bottom cursor-pointer suggestion-item';
                        suggestionItem.style.cursor = 'pointer';
                        suggestionItem.textContent = suggestion.value;
                        
                        suggestionItem.addEventListener('click', function() {
                            addressInput.value = suggestion.value;
                            suggestionsContainer.style.display = 'none';
                        });
                        
                        suggestionItem.addEventListener('mouseenter', function() {
                            this.style.backgroundColor = '#f8f9fa';
                        });
                        
                        suggestionItem.addEventListener('mouseleave', function() {
                            this.style.backgroundColor = 'white';
                        });
                        
                        suggestionsContainer.appendChild(suggestionItem);
                    });
                    
                    suggestionsContainer.style.display = 'block';
                } else {
                    console.log('[v0] Подсказки не найдены');
                    suggestionsContainer.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('[v0] Ошибка получения подсказок:', error);
                suggestionsContainer.innerHTML = '<div class="p-2 text-danger">Ошибка сети: ' + error.message + '</div>';
                suggestionsContainer.style.display = 'block';
            });
    }

    // Обработчик ввода в поле адреса с задержкой
    addressInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            fetchAddressSuggestions(query);
        }, 300);
    });

    // Скрыть подсказки при клике вне поля
    document.addEventListener('click', function(event) {
        if (!addressInput.contains(event.target) && !suggestionsContainer.contains(event.target)) {
            suggestionsContainer.style.display = 'none';
        }
    });

    if (deliveryRadio) {
        deliveryRadio.addEventListener('change', toggleAddressCard);
    }
    if (pickupRadio) {
        pickupRadio.addEventListener('change', toggleAddressCard);
    }

    // Проверяем при загрузке страницы
    toggleAddressCard();

    // Валидация формы
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

function testBasicApi() {
    console.log('[v0] Базовый API тест...');
    
    fetch('/api/test')
        .then(response => {
            console.log('[v0] Статус ответа базового API:', response.status);
            console.log('[v0] Заголовки ответа:', response.headers);
            return response.text();
        })
        .then(text => {
            console.log('[v0] Сырой ответ базового API:', text);
            try {
                const data = JSON.parse(text);
                console.log('[v0] Результат базового API теста:', data);
                showDebugResults('Базовый API тест успешен:\n' + JSON.stringify(data, null, 2));
            } catch (e) {
                console.error('[v0] Ошибка парсинга JSON:', e);
                showDebugResults('Ошибка парсинга JSON. Сырой ответ:\n' + text);
            }
        })
        .catch(error => {
            console.error('[v0] Ошибка базового API теста:', error);
            showDebugResults('Ошибка сети: ' + error.message);
        });
}

function testSimple() {
    console.log('[v0] Простой тест...');
    
    fetch('/api/simple-test')
        .then(response => {
            console.log('[v0] Статус ответа простого теста:', response.status);
            console.log('[v0] Заголовки ответа:', response.headers);
            return response.text();
        })
        .then(text => {
            console.log('[v0] Сырой ответ простого теста:', text);
            try {
                const data = JSON.parse(text);
                console.log('[v0] Результат простого теста:', data);
                showDebugResults(JSON.stringify(data, null, 2));
            } catch (e) {
                console.error('[v0] Ошибка парсинга JSON:', e);
                showDebugResults('Ошибка парсинга JSON. Сырой ответ:\n' + text);
            }
        })
        .catch(error => {
            console.error('[v0] Ошибка простого теста:', error);
            showDebugResults('Ошибка сети: ' + error.message);
        });
}

function testDaDataConfig() {
    console.log('[v0] Тестирование конфигурации DaData...');
    
    fetch('/api/dadata-test-config')
        .then(response => response.json())
        .then(data => {
            console.log('[v0] Результат тестирования конфигурации:', data);
            showDebugResults(JSON.stringify(data, null, 2));
        })
        .catch(error => {
            console.error('[v0] Ошибка тестирования конфигурации:', error);
            showDebugResults('Ошибка: ' + error.message);
        });
}

function testDaDataApi() {
    console.log('[v0] Тестирование API DaData...');
    
    fetch('/api/dadata-test-api')
        .then(response => response.json())
        .then(data => {
            console.log('[v0] Результат тестирования API:', data);
            showDebugResults(JSON.stringify(data, null, 2));
        })
        .catch(error => {
            console.error('[v0] Ошибка тестирования API:', error);
            showDebugResults('Ошибка сети: ' + error.message);
        });
}

function showDebugResults(text) {
    const debugResults = document.getElementById('debug-results');
    const debugOutput = document.getElementById('debug-output');
    
    debugOutput.textContent = text;
    debugResults.style.display = 'block';
}
</script>
@endsection
