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
                <div class="secondary-font text-primary text-uppercase mb-4">Целых 10% =)</div>
                <h2 class="banner-title display-1 fw-normal">Дарим скидку <span class="text-primary"><br>На первый заказ<br></span>
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
                <div class="secondary-font text-primary text-uppercase mb-4">Не текст</div>
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
                            <div class="card position-relative" style="height: 520px;">
                                <a href="{{ route('product.show', $product->id) }}">
                                    <div class="product-image-container" style="height: 280px; overflow: hidden; border-radius: 1rem;">
                                        <img src="{{ asset('storage/' . $product->img_product) }}" 
                                             class="img-fluid w-100 h-100" 
                                             style="object-fit: cover; object-position: center;" 
                                             alt="{{ $product->name_product }}">
                                    </div>
                                </a>
                                <div class="card-body p-0 d-flex flex-column" style="height: 240px;">
                                    <a href="{{ route('product.show', $product->id) }}">
                                        <h3 class="card-title pt-4 m-0" style="height: 60px; overflow: hidden;">{{ $product->name_product }}</h3>
                                    </a>
                                    <div class="card-text flex-grow-1 d-flex flex-column justify-content-between">
                                        <span class="rating secondary-font mb-3" style="height: 57px; overflow: hidden; font-size: 0.85rem; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; line-height: 1.5; text-overflow: ellipsis; word-wrap: break-word; color: #6c757d;">
                                            {{ $product->description_product }}
                                        </span>
                                        <div class="mt-auto">
                                            <h3 class="secondary-font text-primary mb-2">{{ $product->price_product }} руб</h3>
                                            <form action="{{ route('cart.add', $product->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-primary btn-sm w-100" style="border-radius: 0.5rem; padding: 0.6rem;">Добавить в корзину</button>
                                            </form>
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

<!-- Добавляем секцию с картой между каталогом товаров и отзывами -->
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
    <script>
        window.onbeforeunload = function() {
            localStorage.setItem('scrollPosition', window.scrollY);
        };
        window.onload = function() {
            const scrollPosition = localStorage.getItem('scrollPosition');
            if (scrollPosition) {
                window.scrollTo(0, parseInt(scrollPosition));
                localStorage.removeItem('scrollPosition');
            }
        };
    </script>
  </section>
@endsection
