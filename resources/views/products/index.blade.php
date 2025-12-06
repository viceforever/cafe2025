@extends('layouts.app')
@section('title') Главная страница @endsection
@section('main_content')
<section id="banner" style="background: #F9F3EC;">
    <div class="container" style="padding-top: 220px;">
      <div class="swiper main-swiper">
        <div class="swiper-wrapper">
          <div class="swiper-slide py-5">
            <div class="row banner-content align-items-center">
              <div class="img-wrapper col-md-5">
                <img src="images/ornot.png" class="img-fluid">
              </div>
              <div class="content-wrapper col-md-7 p-5 mb-5">
                <h2 class="banner-title display-1 fw-normal">Добро <span class="text-primary">пожаловать!</span>
                </h2>
              </div>
            </div>
          </div>
          <div class="swiper-slide py-5">
            <div class="row banner-content align-items-center">
              <div class="img-wrapper col-md-5">
                <img src="images/ornot.png" class="img-fluid">
              </div>
              <div class="content-wrapper col-md-7 p-5 mb-5">
                <div class="secondary-font text-primary text-uppercase mb-4">Можно использовать для оплаты 50% от суммы заказа</div>
                <h2 class="banner-title display-1 fw-normal">Дарим бонусы <span class="text-primary"><br>При регистрации!<br></span>
                </h2>
              </div>
            </div>
          </div>
          <div class="swiper-slide py-5">
            <div class="row banner-content align-items-center">
              <div class="img-wrapper col-md-5">
                <img src="images/ornot.png" class="img-fluid">
              </div>
              <div class="content-wrapper col-md-7 p-5 mb-5">
                <div class="secondary-font text-primary text-uppercase mb-4">Маленький текст</div>
                <h2 class="banner-title display-1 fw-normal">Текст <span class="text-primary">еще текст</span>
                </h2>
              </div>
            </div>
          </div>
        </div>
        <div class="swiper-pagination mb-5"></div>
      </div>
    </div>
  </section>
<div class="container">
    <!-- Блок для отображения уведомлений -->
    
    @if(isset($query))
        <h2>Результаты поиска для: {{ $query }}</h2>
    @endif
    @if($products->isEmpty())
        <p>Ничего не найдено.</p>
    @else
    @foreach($categories as $category)
    @if($products->has($category->id))
        <section id="{{ Str::slug($category->name_category) }}" class="my-5">
            <div class="container my-5 py-5">
                <div class="section-header d-md-flex justify-content-between align-items-center">
                    <h2 class="display-3 fw-normal">{{ $category->name_category }}</h2>
                </div>
                <div class="isotope-container row">
                    @foreach($products[$category->id] as $product)
                        <div class="item cat col-md-4 col-lg-3 my-4">
                            <div class="card position-relative" style="height: 560px;">
                                <a href="{{ route('product.show', $product->id) }}">
                                    <div class="product-image-container" style="height: 280px; overflow: hidden; border-radius: 1rem;">
                                        <img src="{{ asset('storage/' . $product->img_product) }}" 
                                             class="img-fluid w-100 h-100" 
                                             style="object-fit: cover; object-position: center;" 
                                             alt="{{ $product->name_product }}">
                                    </div>
                                </a>
                                <div class="card-body p-0 d-flex flex-column" style="height: 280px;">
                                    <a href="{{ route('product.show', $product->id) }}">
                                        <!-- Increased height to 95px for proper 2-line display with larger font -->
                                        <h3 class="card-title pt-4 m-0" style="height: 95px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; line-height: 1.5; word-break: normal; overflow-wrap: break-word; font-size: 1.5rem;">{{ $product->name_product }}</h3>
                                    </a>
                                    <div class="card-text flex-grow-1 d-flex flex-column justify-content-between">
                                        <!-- Removed mt-2 margin to reduce spacing between title and description -->
                                        <span class="rating secondary-font mb-3" style="height: 80px; overflow: hidden; font-size: 0.9rem; display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical; line-height: 1.4; text-overflow: ellipsis; word-wrap: break-word; color: #6c757d;">
                                            {{ $product->description_product }}
                                        </span>
                                        <div class="mt-auto">
                                            <h3 class="secondary-font text-primary mb-2">{{ $product->price_product }} руб</h3>
                                            <!-- Заменил форму на кнопку с AJAX для обработки ошибок добавления -->
                                            @if($product->isAvailable())
                                                <!-- Кнопка добавления активна при наличии ингредиентов -->
                                                <button type="button" class="btn btn-primary btn-sm w-100 add-to-cart-main" 
                                                        data-product-id="{{ $product->id }}"
                                                        style="border-radius: 0.5rem; padding: 0.6rem;">
                                                    Добавить в корзину
                                                </button>
                                            @else
                                                <!-- Кнопка заблокирована при нехватке ингредиентов -->
                                                <button type="button" class="btn btn-secondary btn-sm w-100 disabled" 
                                                        disabled
                                                        style="border-radius: 0.5rem; padding: 0.6rem;">
                                                    Нет в наличии
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endforeach
@endif

<!-- Секция с картой -->
<section id="location" class="my-5" style="background: #F9F3EC;">
    <div class="container my-5 py-5">
        <div class="section-header text-center mb-5">
            <h2 class="display-3 fw-normal">Наше <span class="text-primary">расположение</span></h2>
            <p class="fs-5 text-muted mt-3">Найдите нас на карте и приходите в гости!</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="map-container" style="border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                    <iframe 
                        id="map_764036322" 
                        frameborder="0" 
                        width="100%" 
                        height="600px" 
                        src="https://makemap.2gis.ru/widget?data=eJw1j09Pg0AQxb_LeJSY3eVPgaQHpbHWNIb2UtH0QMoU12xZsmyplPS7O4DOaTK_Ny_v9aBNgQaLJeoTWiOxgfizB9vVCDE8Y27PBsGB2ugajR15DwettCF-dzxGR8aIW2nV8EFrgc3ByNpKXdEhfXm8Z6_XrewWm7ZILix737Q2YWz9vWnflk8sC0uWrbdltExWC1anSXRJy_mcjK6rqsAfiDn7n5sD5RS0G2L8pUy1rCzpD5rKyCq3YwnOvAcRch45vqDFFcLf078sIA6Fe9s7cMrrVDdyCtqDyi3Eo5YHIQtc4YazKHBADXhy86KZyz3Pd7kgcNX6RMQnV2qsldp9IaqP8WrNGW-__2ZrIg" 
                        sandbox="allow-modals allow-forms allow-scripts allow-same-origin allow-popups allow-top-navigation-by-user-activation"
                        style="border: none;">
                    </iframe>
                </div>
                <div class="text-center mt-4">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="d-flex align-items-center justify-content-center">
                                <iconify-icon icon="mdi:map-marker" class="text-primary fs-4 me-2"></iconify-icon>
                                <div>
                                    <h6 class="mb-0">Адрес</h6>
                                    <small class="text-muted">г. Иркутск, ул. Ленина, 5А</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="d-flex align-items-center justify-content-center">
                                <iconify-icon icon="mdi:clock-outline" class="text-primary fs-4 me-2"></iconify-icon>
                                <div>
                                    <h6 class="mb-0">Режим работы</h6>
                                    <small class="text-muted">Ежедневно 10:00 - 22:00</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="d-flex align-items-center justify-content-center">
                                <iconify-icon icon="mdi:phone" class="text-primary fs-4 me-2"></iconify-icon>
                                <div>
                                    <h6 class="mb-0">Телефон</h6>
                                    <small class="text-muted">+7 (950) 123-45-67</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

  <section id="testimonial">
    <div class="container my-5 py-5">
      <div class="row">
        <div class="offset-md-1 col-md-10">
          <div class="swiper testimonial-swiper">
            <div class="swiper-wrapper">
              <div class="swiper-slide">
                <div class="row ">
                  <div class="col-2">
                    <iconify-icon icon="mingcute:dinner-line" class="quote-icon text-primary"></iconify-icon>
                  </div>
                  <div class="col-md-10 mt-md-5 p-5 pt-0 pt-md-5">
                    <p class="testimonial-content fs-2">Мы разработали более {{ $totalProducts }} разновидностей фирменных блюд с неповторимым вкусом и продолжаем расширять ассортимент.</p>
                  </div>
                </div>
              </div>
              <div class="swiper-slide">
                <div class="row ">
                  <div class="col-2">
                    <iconify-icon icon="mingcute:dinner-line" class="quote-icon text-primary"></iconify-icon>
                  </div>
                  <div class="col-md-10 mt-md-5 p-5 pt-0 pt-md-5">
                    <p class="testimonial-content fs-2">Тут будет умный текст.</p>
                  </div>
                </div>
              </div>
              <div class="swiper-slide">
                <div class="row ">
                  <div class="col-2">
                    <iconify-icon icon="mingcute:dinner-line" class="quote-icon text-primary"></iconify-icon>
                  </div>
                  <div class="col-md-10 mt-md-5 p-5 pt-0 pt-md-5">
                    <p class="testimonial-content fs-2">И тут тоже.</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="swiper-pagination"></div>
          </div>
        </div>
      </div>
    </div>
  </section>

<!-- Добавил AJAX обработчик для добавления товара в корзину с главной страницы -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Функция для отображения сообщений об ошибках
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
        const iconColor = isError ? '#dc3545' : '#28a745';
        const title = isError ? 'Ошибка' : 'Успешно';
        const iconPath = isError 
            ? 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z'
            : 'M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z';
        
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
    
    // Обработчик для кнопок "Добавить в корзину" на главной странице
    document.querySelectorAll('.add-to-cart-main').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const productId = this.dataset.productId;
            const originalText = this.textContent;
            const btn = this;
            
            btn.disabled = true;
            btn.textContent = 'Добавление...';
            
            fetch(`/cart/add/${productId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ quantity: 1 })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showToast('Товар добавлен в корзину', 'success');
                    
                    // Обновляем счетчик корзины в шапке
                    const cartCountElement = document.querySelector('.cart-count');
                    if (cartCountElement && data.cart_count) {
                        cartCountElement.textContent = data.cart_count;
                        cartCountElement.style.display = 'flex';
                    }
                } else {
                    // Показываем сообщение об ошибке от сервера
                    showToast(data.message || 'Ошибка при добавлении товара', 'error');
                }
                btn.textContent = originalText;
                btn.disabled = false;
            })
            .catch(error => {
                console.error('Ошибка:', error);
                showToast('Произошла ошибка при добавлении товара в корзину', 'error');
                btn.textContent = originalText;
                btn.disabled = false;
            });
        });
    });
});
</script>
@endsection
