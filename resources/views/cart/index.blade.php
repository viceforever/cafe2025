@extends('layouts.app')

@section('title', 'Корзина')

@section('main_content')
<div class="container min-vh-100 d-flex flex-column" style="margin-top: 120px">
    <section id="cart" class="my-5 py-5 flex-grow-1" style="margin-top: 120px; margin-bottom: 50px;">
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
                        @if (count($cart) > 0)
                            <a href="{{ route('checkout') }}" class="btn btn-primary" id="checkout-btn">Оформить заказ</a>
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
    
    function updateCheckoutButton(cartCount) {
        if (cartCount > 0) {
            checkoutBtn.classList.remove('btn-secondary');
            checkoutBtn.classList.add('btn-primary');
            checkoutBtn.disabled = false;
            checkoutBtn.textContent = 'Оформить заказ';
            checkoutBtn.href = '{{ route("checkout") }}';
        } else {
            checkoutBtn.classList.remove('btn-primary');
            checkoutBtn.classList.add('btn-secondary');
            checkoutBtn.disabled = true;
            checkoutBtn.textContent = 'Корзина пуста';
            checkoutBtn.removeAttribute('href');
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
        
        toast.innerHTML = `
            <div class="toast-header">
                <div class="toast-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
                    </svg>
                </div>
                <strong class="me-auto">Успешно</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        `;
        
        toastContainer.appendChild(toast);
        document.body.appendChild(toastContainer);
        
        const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
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
                if (data.items_html) {
                    cartItemsContainer.innerHTML = data.items_html;
                }
                
                if (paginationContainer && data.pagination_html) {
                    paginationContainer.innerHTML = data.pagination_html;
                }
                
                if (data.needs_redirect) {
                    window.location.href = `{{ route('cart.index') }}?page=${data.redirect_page}`;
                    return;
                }
                
                cartTotalElement.textContent = data.cart_total;
                updateCartCount(data.cart_count);
                updateCheckoutButton(data.cart_count);
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
                if (data.items_html) {
                    cartItemsContainer.innerHTML = data.items_html;
                }
                
                if (paginationContainer && data.pagination_html) {
                    paginationContainer.innerHTML = data.pagination_html;
                }
                
                if (data.needs_redirect) {
                    window.location.href = `{{ route('cart.index') }}?page=${data.redirect_page}`;
                    return;
                }
                
                cartTotalElement.textContent = data.cart_total;
                updateCartCount(data.cart_count);
                updateCheckoutButton(data.cart_count);
                
                showToast('Товар удален из корзины');
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
