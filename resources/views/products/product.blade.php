@extends('layouts.app')
@section('title') Просмотр товара @endsection

@section('main_content')
<div class="container min-vh-100 d-flex flex-column">
  <section id="selling-product" class="flex-grow-1" style="margin-top: 220px; margin-bottom: 50px">
    
    <div class="row g-md-5">
      <div class="col-lg-6">
        <div class="row">
          <div class="col-md-12">
            <div class="swiper product-large-slider swiper-fade swiper-initialized swiper-horizontal swiper-watch-progress swiper-backface-hidden">
              <div class="swiper-wrapper" id="swiper-wrapper-cf7fd33d98ca8c79" aria-live="polite">
                <div class="swiper-slide swiper-slide-visible swiper-slide-active" role="group" aria-label="1 / 4" style="width: 444px; opacity: 1; transform: translate3d(0px, 0px, 0px);">
                  <img src="{{ Storage::url($product->img_product) }}" class="img-fluid">
                </div>
                <div class="swiper-slide swiper-slide-next" role="group" aria-label="2 / 4" style="width: 444px; opacity: 0; transform: translate3d(-444px, 0px, 0px);">
                  <img src="{{ Storage::url($product->img_product) }}" class="img-fluid">
                </div>
                <div class="swiper-slide" role="group" aria-label="3 / 4" style="width: 444px; opacity: 0; transform: translate3d(-888px, 0px, 0px);">
                  <img src="{{ Storage::url($product->img_product) }}" class="img-fluid">
                </div>
                <div class="swiper-slide" role="group" aria-label="4 / 4" style="width: 444px; opacity: 0; transform: translate3d(-1332px, 0px, 0px);">
                  <img src="{{ Storage::url($product->img_product) }}" class="img-fluid">
                </div>
              </div>
              <span class="swiper-notification" aria-live="assertive" aria-atomic="true"></span>
            </div>
          </div>
          <div class="col-md-12 mt-2">
            <div thumbsslider="" class="swiper product-thumbnail-slider swiper-initialized swiper-horizontal swiper-free-mode swiper-watch-progress swiper-backface-hidden">
              <div class="swiper-wrapper" id="swiper-wrapper-c8f952a109eb6c76" aria-live="polite" style="transform: translate3d(0px, 0px, 0px);">
              </div>
              <span class="swiper-notification" aria-live="assertive" aria-atomic="true"></span>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-6 mt-5">
        <div class="product-info">
          <div class="element-header">
            <h2 itemprop="name" class="display-6">{{ $product->name_product }}</h2>
          </div>
          <div class="product-price pt-3 pb-3">
            <strong class="text-primary display-6 fw-bold">{{ $product->price_product }} руб</strong>
          </div>
          <p style="word-wrap: break-word; overflow-wrap: break-word; white-space: normal;">{{ $product->description_product }}</p>
          <div class="cart-wrap">
            <div class="product-quantity pt-2">
              <div class="stock-button-wrap">
                <form id="addToCartForm">
                  @csrf
                  <div class="input-group product-qty align-items-center w-25 mb-3">
                    <span class="input-group-btn">
                      <button type="button" class="quantity-left-minus btn btn-light btn-number" data-type="minus">
                        <svg width="16" height="16">
                          <use xlink:href="#minus"></use>
                        </svg>
                      </button>
                    </span>
                    <input type="number" id="quantity" name="quantity" class="form-control input-number text-center p-2 mx-1" value="1" min="1" readonly>
                    <span class="input-group-btn">
                      <button type="button" class="quantity-right-plus btn btn-light btn-number" data-type="plus">
                        <svg width="16" height="16">
                          <use xlink:href="#plus"></use>
                        </svg>
                      </button>
                    </span>
                  </div>
                  @if($product->isAvailable())
                    <button type="submit" class="btn btn-primary" id="add-to-cart-btn">Добавить в корзину</button>
                  @else
                    <button type="button" class="btn btn-secondary disabled" disabled>Нет в наличии</button>
                  @endif
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const quantityInput = document.getElementById('quantity');
    const minusBtn = document.querySelector('.quantity-left-minus');
    const plusBtn = document.querySelector('.quantity-right-plus');
    const form = document.getElementById('addToCartForm');
    const addToCartBtn = document.getElementById('add-to-cart-btn');
    
    const productId = {{ $product->id }};
    const maxAvailableQuantity = {{ $maxAvailableQuantity }};
    const quantityInCart = {{ $quantityInCart }};
    const availableToAdd = {{ $availableToAdd }};
    
    minusBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        let currentValue = parseInt(quantityInput.value);
        if (currentValue > 1) {
            quantityInput.value = currentValue - 1;
        }
        return false;
    }, true);
    
    plusBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        
        let currentValue = parseInt(quantityInput.value);
        let newValue = currentValue + 1;
        
        fetch(`/product/${productId}/check-quantity`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ quantity: newValue })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                quantityInput.value = newValue;
            } else {
                showToast(data.message || 'Невозможно увеличить количество', 'warning');
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            showToast('Произошла ошибка при проверке количества', 'error');
        });
        
        return false;
    }, true);
    
    form.addEventListener('submit', function(e) {
        if (addToCartBtn.disabled) {
            return;
        }
        
        e.preventDefault();
        
        const quantity = parseInt(quantityInput.value);
        const originalText = addToCartBtn.textContent;
        
        addToCartBtn.disabled = true;
        addToCartBtn.textContent = 'Добавление...';
        
        fetch(`/cart/add/${productId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ quantity: quantity })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showToast('Товар добавлен в корзину');
                
                const cartCountElement = document.querySelector('.cart-count');
                if (cartCountElement && data.cart_count) {
                    cartCountElement.textContent = data.cart_count;
                    cartCountElement.style.display = 'flex';
                }
                
                quantityInput.value = 1;
            } else {
                showToast(data.message || 'Ошибка при добавлении товара', 'warning');
            }
            addToCartBtn.textContent = originalText;
            addToCartBtn.disabled = false;
        })
        .catch(error => {
            console.error('Ошибка:', error);
            showToast('Произошла ошибка при добавлении товара в корзину', 'error');
            addToCartBtn.textContent = originalText;
            addToCartBtn.disabled = false;
        });
    });
    
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
        
        const isError = type === 'error' || type === 'warning';
        const isWarning = type === 'warning';
        const iconColor = isError && !isWarning ? '#dc3545' : (isWarning ? '#ffc107' : '#28a745');
        const title = isError && !isWarning ? 'Ошибка' : (isWarning ? 'Внимание' : 'Успешно');
        const iconPath = isError && !isWarning
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
});
</script>
@endsection
