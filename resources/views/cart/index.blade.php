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
                        @if (session('cart') && count(session('cart')) > 0)
                            @foreach (session('cart') as $id => $item)
                                <tr data-item-id="{{ $id }}">
                                    <td scope="row" class="py-4">
                                        <div class="cart-info d-flex flex-wrap align-items-center">
                                            <div class="col-lg-3">
                                                <div class="card-image">
                                                    <img src="{{ $item['img_product'] ? Storage::url($item['img_product']) : asset('default-image.jpg') }}" alt="{{ $item['name'] }}" class="img-fluid">
                                                </div>
                                            </div>
                                            <div class="col-lg-9">
                                                <div class="cart-detail ps-3">
                                                    <h5 class="card-title">
                                                        <a href="{{ route('product.show', $item['id']) }}" class="text-decoration-none">{{ $item['name'] }}</a>
                                                    </h5>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 align-middle">
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            <button type="button" class="btn btn-outline-secondary btn-sm d-flex align-items-center justify-content-center cart-decrease" 
                                                    data-item-id="{{ $id }}" style="width: 32px; height: 32px; padding: 0;">
                                                <svg width="16" height="16">
                                                    <use xlink:href="#minus"></use>
                                                </svg>
                                            </button>

                                            <input type="text" class="form-control text-center item-quantity" style="width: 60px; padding: 6px;" value="{{ $item['quantity'] }}" readonly>
                                            
                                            <button type="button" class="btn btn-outline-secondary btn-sm d-flex align-items-center justify-content-center cart-increase" 
                                                    data-item-id="{{ $id }}" style="width: 32px; height: 32px; padding: 0;">
                                                <svg width="16" height="16">
                                                    <use xlink:href="#plus"></use>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="py-4 align-middle">
                                        <div class="total-price">
                                            <!-- Added white-space: nowrap to prevent ruble symbol wrapping -->
                                            <span class="secondary-font fw-medium item-total" style="white-space: nowrap;">{{ $item['price'] * $item['quantity'] }} ₽</span>
                                        </div>
                                    </td>
                                    <td class="py-4 align-middle">
                                        <div class="cart-remove">
                                            <button type="button" class="btn btn-link p-0 cart-remove-btn" data-item-id="{{ $id }}">
                                                <svg width="24" height="24">
                                                    <use xlink:href="#trash"></use>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr id="empty-cart-message">
                                <td colspan="4" class="py-4 text-center">Корзина пуста.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="col-md-4">
                <div class="cart-totals">
                    <h2 class="pb-4">Итоговая стоимость:</h2>
                    <div class="total-price pb-4">
                        <!-- Simplified structure with flexbox to prevent wrapping -->
                        <div style="border-top: 1px solid #dee2e6; border-bottom: 1px solid #dee2e6; padding: 1rem 0; display: flex; align-items: center; justify-content: flex-start;">
                            <div style="font-size: 1.25rem; white-space: nowrap; display: inline-block;">
                                <span id="cart-total">{{ array_reduce(session('cart', []), function ($carry, $item) {
                                    return $carry + ($item['price'] * $item['quantity']);
                                }, 0) }}</span> ₽
                            </div>
                        </div>
                    </div>
                    <div>
                        @if (session('cart') && count(session('cart')) > 0)
                            <a href="{{ route('checkout') }}" class="btn btn-primary">Оформить заказ</a>
                        @else
                            <button class="btn btn-secondary" disabled>Корзина пуста</button>
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
        fetch(`/cart/update/${itemId}`, {
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
                if (data.item_removed) {
                    const row = document.querySelector(`tr[data-item-id="${itemId}"]`);
                    if (row) {
                        row.style.transition = 'opacity 0.3s';
                        row.style.opacity = '0';
                        setTimeout(() => {
                            row.remove();
                            checkEmptyCart();
                        }, 300);
                    }
                } else {
                    const row = document.querySelector(`tr[data-item-id="${itemId}"]`);
                    if (row) {
                        row.querySelector('.item-quantity').value = data.item_quantity;
                        row.querySelector('.item-total').textContent = `${data.item_total} ₽`;
                    }
                }
                
                cartTotalElement.textContent = data.cart_total;
                updateCartCount(data.cart_count);
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            showToast('Произошла ошибка при обновлении корзины', 'danger');
        });
    }
    
    function removeCartItem(itemId) {
        fetch(`/cart/remove/${itemId}`, {
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
                const row = document.querySelector(`tr[data-item-id="${itemId}"]`);
                if (row) {
                    row.style.transition = 'opacity 0.3s';
                    row.style.opacity = '0';
                    setTimeout(() => {
                        row.remove();
                        checkEmptyCart();
                    }, 300);
                }
                
                cartTotalElement.textContent = data.cart_total;
                updateCartCount(data.cart_count);
                
                showToast('Товар удален из корзины');
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            showToast('Произошла ошибка при удалении товара', 'danger');
        });
    }
    
    function checkEmptyCart() {
        const rows = cartItemsContainer.querySelectorAll('tr[data-item-id]');
        if (rows.length === 0) {
            cartItemsContainer.innerHTML = '<tr id="empty-cart-message"><td colspan="4" class="py-4 text-center">Корзина пуста.</td></tr>';
        }
    }
});
</script>
@endsection
