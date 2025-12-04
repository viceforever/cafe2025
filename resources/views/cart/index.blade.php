@extends('layouts.app')

@section('title', 'Корзина')

@section('main_content')
<div class="container min-vh-100 d-flex flex-column" style="margin-top: 120px">
    <section id="cart" class="my-5 py-5 flex-grow-1" style="margin-top: 120px; margin-bottom: 50px;">
        {{-- Добавляем предупреждение о недоступных товарах --}}
        <div id="unavailable-alert" class="alert alert-warning mb-4" style="background-color: #fff3cd; border-color: #ffecb5; color: #856404; @if(empty($unavailableItems)) display: none; @endif">
            <div class="d-flex align-items-start">
                <i class="iconify fs-4 me-3" data-icon="mdi:alert-circle-outline"></i>
                <div id="unavailable-content">
                    <strong>Внимание!</strong> Следующие товары в вашей корзине временно недоступны из-за нехватки ингредиентов:
                    <ul class="mb-0 mt-2" id="unavailable-list">
                        @foreach($unavailableItems as $itemName)
                            <li>{{ $itemName }}</li>
                        @endforeach
                    </ul>
                    <small class="d-block mt-2">Пожалуйста, удалите эти товары или уменьшите количество, чтобы оформить заказ.</small>
                </div>
            </div>
        </div>
        
        <div class="row g-md-5">
            <div class="col-md-8 pe-md-5">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col" class="card-title text-uppercase">Товар</th>
                            <th scope="col" class="card-title text-uppercase">Количество</th>
                            <th scope="col" class="card-title text-uppercase">Итого</th>
                            <th scope="col" class="card-title text-uppercase"></th>
                        </tr>
                    </thead>
                    <tbody id="cart-items">
                        @include('cart.partials.items', ['paginatedCart' => $paginatedCart])
                    </tbody>
                </table>
                
                <div id="pagination-container">
                    @include('cart.partials.pagination', ['paginatedCart' => $paginatedCart])
                </div>
            </div>
            <div class="col-md-4">
                <div class="cart-totals">
                    <h2 class="pb-4">Итоговая стоимость:</h2>
                    <div class="total-price pb-4">
                        <div style="border-top: 1px solid #dee2e6; border-bottom: 1px solid #dee2e6; padding: 1rem 0; display: flex; align-items: center; justify-content: flex-start;">
                            <div style="font-size: 1.25rem; white-space: nowrap; display: inline-block;">
                                <span id="cart-total">{{ array_reduce($cart, function ($carry, $item) {
                                    return $carry + ($item['price'] * $item['quantity']);
                                }, 0) }}</span> ₽
                            </div>
                        </div>
                    </div>
                    <div>
                        {{-- Блокируем кнопку если есть недоступные товары --}}
                        @if (count($cart) > 0 && empty($unavailableItems))
                            <a href="{{ route('checkout') }}" class="btn btn-primary" id="checkout-btn">Оформить заказ</a>
                        @elseif (!empty($unavailableItems))
                            <button class="btn btn-secondary" disabled id="checkout-btn" style="background-color: #6c757d; border-color: #6c757d; cursor: not-allowed;">
                                Товары недоступны
                            </button>
                        @else
                            <button class="btn btn-secondary" disabled id="checkout-btn">Корзина пуста</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cartItemsContainer = document.getElementById('cart-items');
    const cartTotalElement = document.getElementById('cart-total');
    const cartCountElement = document.querySelector('.cart-count');
    const paginationContainer = document.getElementById('pagination-container');
    const checkoutBtn = document.getElementById('checkout-btn');
    const unavailableAlert = document.getElementById('unavailable-alert');
    
    function getCurrentPage() {
        const urlParams = new URLSearchParams(window.location.search);
        return parseInt(urlParams.get('page')) || 1;
    }
    
    function updateCartCount(count) {
        if (cartCountElement) {
            cartCountElement.textContent = count;
            if (count > 0) {
                cartCountElement.style.display = 'flex';
            } else {
                cartCountElement.style.display = 'none';
            }
        }
    }
    
    function updateCheckoutButton(cartCount, hasUnavailable = false) {
        // Обновляем видимость предупреждения
        if (hasUnavailable) {
            unavailableAlert.style.display = 'block';
        } else {
            unavailableAlert.style.display = 'none';
        }
        
        // Обновляем состояние кнопки
        if (cartCount > 0 && !hasUnavailable) {
            checkoutBtn.classList.remove('btn-secondary');
            checkoutBtn.classList.add('btn-primary');
            checkoutBtn.disabled = false;
            checkoutBtn.textContent = 'Оформить заказ';
            if (checkoutBtn.tagName === 'BUTTON') {
                const newLink = document.createElement('a');
                newLink.href = '{{ route("checkout") }}';
                newLink.className = 'btn btn-primary';
                newLink.id = 'checkout-btn';
                newLink.textContent = 'Оформить заказ';
                checkoutBtn.parentNode.replaceChild(newLink, checkoutBtn);
            } else {
                checkoutBtn.href = '{{ route("checkout") }}';
            }
        } else if (hasUnavailable) {
            if (checkoutBtn.tagName === 'A') {
                const newBtn = document.createElement('button');
                newBtn.className = 'btn btn-secondary';
                newBtn.id = 'checkout-btn';
                newBtn.disabled = true;
                newBtn.textContent = 'Товары недоступны';
                newBtn.style.backgroundColor = '#6c757d';
                newBtn.style.borderColor = '#6c757d';
                newBtn.style.cursor = 'not-allowed';
                checkoutBtn.parentNode.replaceChild(newBtn, checkoutBtn);
            } else {
                checkoutBtn.classList.remove('btn-primary');
                checkoutBtn.classList.add('btn-secondary');
                checkoutBtn.disabled = true;
                checkoutBtn.textContent = 'Товары недоступны';
                checkoutBtn.style.backgroundColor = '#6c757d';
                checkoutBtn.style.borderColor = '#6c757d';
                checkoutBtn.style.cursor = 'not-allowed';
            }
        } else {
            if (checkoutBtn.tagName === 'A') {
                const newBtn = document.createElement('button');
                newBtn.className = 'btn btn-secondary';
                newBtn.id = 'checkout-btn';
                newBtn.disabled = true;
                newBtn.textContent = 'Корзина пуста';
                checkoutBtn.parentNode.replaceChild(newBtn, checkoutBtn);
            } else {
                checkoutBtn.classList.remove('btn-primary');
                checkoutBtn.classList.add('btn-secondary');
                checkoutBtn.disabled = true;
                checkoutBtn.textContent = 'Корзина пуста';
                checkoutBtn.style.backgroundColor = '';
                checkoutBtn.style.borderColor = '';
                checkoutBtn.style.cursor = '';
            }
        }
    }
    
    function showToast(message, type = 'success') {
        const existingToast = document.querySelector('.custom-toast');
        if (existingToast) {
            existingToast.remove();
        }
        
        const toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container';
        toastContainer.style.cssText = 'position: fixed; top: 100px; right: 20px; z-index: 9999;';
        
        const toast = document.createElement('div');
        toast.className = 'toast custom-toast show';
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        const isError = type === 'error' || type === 'danger';
        const isWarning = type === 'warning';
        const iconColor = isError ? '#dc3545' : (isWarning ? '#ffc107' : '#28a745');
        const title = isError ? 'Ошибка' : (isWarning ? 'Внимание' : 'Успешно');
        const iconPath = isError 
            ? 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z'
            : (isWarning 
                ? 'M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z'
                : 'M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z');
        
        toast.innerHTML = `
            <div class="toast-header">
                <div class="toast-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="${iconColor}">
                        <path d="${iconPath}"/>
                    </svg>
                </div>
                <strong class="me-auto">${title}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        `;
        
        toastContainer.appendChild(toast);
        document.body.appendChild(toastContainer);
        
        const bsToast = new bootstrap.Toast(toast, { delay: 5000 });
        bsToast.show();
        
        toast.addEventListener('hidden.bs.toast', function() {
            toastContainer.remove();
        });
    }
    
    cartItemsContainer.addEventListener('click', function(e) {
        const increaseBtn = e.target.closest('.cart-increase');
        if (increaseBtn) {
            const itemId = increaseBtn.dataset.itemId;
            updateCartItem(itemId, 'increase');
        }
    });
    
    cartItemsContainer.addEventListener('click', function(e) {
        const decreaseBtn = e.target.closest('.cart-decrease');
        if (decreaseBtn) {
            const itemId = decreaseBtn.dataset.itemId;
            updateCartItem(itemId, 'decrease');
        }
    });
    
    cartItemsContainer.addEventListener('click', function(e) {
        const removeBtn = e.target.closest('.cart-remove-btn');
        if (removeBtn) {
            const itemId = removeBtn.dataset.itemId;
            removeCartItem(itemId);
        }
    });
    
    function updateCartItem(itemId, action) {
        const currentPage = getCurrentPage();
        
        fetch(`/cart/update/${itemId}?page=${currentPage}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ action: action })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.needs_redirect || data.item_removed) {
                    window.location.href = `{{ route('cart.index') }}?page=${data.redirect_page || currentPage}`;
                    return;
                }
                
                if (data.items_html) {
                    cartItemsContainer.innerHTML = data.items_html;
                }
                
                if (paginationContainer && data.pagination_html) {
                    paginationContainer.innerHTML = data.pagination_html;
                }
                
                cartTotalElement.textContent = data.cart_total;
                updateCartCount(data.cart_count);
                
                updateCheckoutButton(data.cart_count, data.has_unavailable_items);
            } else {
                showToast(data.message || 'Невозможно увеличить количество', 'warning');
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            showToast('Произошла ошибка при обновлении корзины', 'danger');
        });
    }
    
    function removeCartItem(itemId) {
        const currentPage = getCurrentPage();
        
        fetch(`/cart/remove/${itemId}?page=${currentPage}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.needs_redirect) {
                    window.location.href = `{{ route('cart.index') }}?page=${data.redirect_page}`;
                } else {
                    if (data.items_html) {
                        cartItemsContainer.innerHTML = data.items_html;
                    }
                    
                    if (paginationContainer && data.pagination_html) {
                        paginationContainer.innerHTML = data.pagination_html;
                    }
                    
                    cartTotalElement.textContent = data.cart_total;
                    updateCartCount(data.cart_count);
                    updateCheckoutButton(data.cart_count, data.has_unavailable_items);
                    
                    showToast('Товар удален из корзины', 'success');
                }
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            showToast('Произошла ошибка при удалении товара', 'danger');
        });
    }
});
</script>
@endsection
