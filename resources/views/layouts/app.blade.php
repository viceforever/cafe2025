<!-- ШАБЛОН ДЛЯ ШАПКИ САЙТА, НА КАЖДОЙ СТРАНИЦЕ -->
<!DOCTYPE html>
<html lang="en">

<head>
    <title>@yield('title')</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="author" content="">
    <meta name="keywords" content="">
    <meta name="description" content="">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">

  <link rel="stylesheet" href="{{ asset('css/swiper.css') }}">
  <link rel="stylesheet" href="{{ asset('css/bootstrap.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('css/vendor.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('css/style.css') }}">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Chilanka&family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- Added custom styles for toast notifications -->
  <style>
    .toast-container {
      position: fixed;
      top: 100px;
      right: 20px;
      z-index: 9999;
    }
    
    .custom-toast {
      min-width: 300px;
      background: white;
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
      border-left: 4px solid #28a745;
      animation: slideIn 0.3s ease-out;
    }
    
    @keyframes slideIn {
      from {
        transform: translateX(400px);
        opacity: 0;
      }
      to {
        transform: translateX(0);
        opacity: 1;
      }
    }
    
    .custom-toast .toast-header {
      background: transparent;
      border-bottom: none;
      padding: 12px 16px 8px;
    }
    
    .custom-toast .toast-body {
      padding: 8px 16px 12px;
      color: #333;
      font-size: 0.95rem;
    }
    
    .toast-icon {
      width: 24px;
      height: 24px;
      background: #28a745;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 10px;
    }
    
    .toast-icon svg {
      width: 14px;
      height: 14px;
      fill: white;
    }
  </style>
</head>

<body>

  <!-- ... existing SVG symbols ... -->
  <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
    <defs>
      <symbol xmlns="http://www.w3.org/2000/svg" id="link" viewBox="0 0 24 24">
        <path fill="currentColor"
          d="M12 19a1 1 0 1 0-1-1a1 1 0 0 0 1 1Zm5 0a1 1 0 1 0-1-1a1 1 0 0 0 1 1Zm0-4a1 1 0 1 0-1-1a1 1 0 0 0 1 1Zm-5 0a1 1 0 1 0-1-1a1 1 0 0 0 1 1Zm7-12h-1V2a1 1 0 0 0-2 0v1H8V2a1 1 0 0 0-2 0v1H5a3 3 0 0 0-3 3v14a3 3 0 0 0 3 3h14a3 3 0 0 0 3-3V6a3 3 0 0 0-3-3Zm1 17a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-9h16Zm0-11H4V6a1 1 0 0 1 1-1h1v1a1 1 0 0 0 2 0V5h8v1a1 1 0 0 0 2 0V5h1a1 1 0 0 1 1 1ZM7 15a1 1 0 1 0-1-1a1 1 0 0 0 1 1Zm0 4a1 1 0 1 0-1-1a1 1 0 0 0 1 1Z" />
      </symbol>
      <!-- ... existing symbols ... -->
      <symbol xmlns="http://www.w3.org/2000/svg" id="plus" viewBox="0 0 24 24">
        <path fill="currentColor" d="M19 11h-6V5a1 1 0 0 0-2 0v6H5a1 1 0 0 0 0 2h6v6a1 1 0 0 0 2 0v-6h6a1 1 0 0 0 0-2Z"/>
      </symbol>
      <symbol xmlns="http://www.w3.org/2000/svg" id="minus" viewBox="0 0 24 24">
        <path fill="currentColor" d="M19 11H5a1 1 0 0 0 0 2h14a1 1 0 0 0 0-2Z"/>
      </symbol>
      <symbol xmlns="http://www.w3.org/2000/svg" id="trash" viewBox="0 0 24 24">
        <path fill="currentColor" d="M10 18a1 1 0 0 0 1-1v-6a1 1 0 0 0-2 0v6a1 1 0 0 0 1 1ZM20 6h-4V5a3 3 0 0 0-3-3h-2a3 3 0 0 0-3 3v1H4a1 1 0 0 0 0 2h1v11a3 3 0 0 0 3 3h8a3 3 0 0 0 3-3V8h1a1 1 0 0 0 0-2ZM10 5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v1h-4Zm7 14a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1V8h10Zm-3-1a1 1 0 0 0 1-1v-6a1 1 0 0 0-2 0v6a1 1 0 0 0 1 1Z"/>
      </symbol>
    </defs>
  </svg>

  <div class="preloader-wrapper">
    <div class="preloader">
    </div>
  </div>

  <header class="sticky-header">
    <div class="container py-2">
      <div class="row py-4 pb-0 pb-sm-4 align-items-center ">

        <div class="col-sm-4 col-lg-3 text-center text-sm-start">
          <div class="main-logo">
            <a href="/">
              <img src="{{ asset('images/logo123.png') }}" alt="logo" class="img-fluid">
            </a>
          </div>
        </div>

        <div class="col-sm-6 offset-sm-2 offset-md-0 col-lg-5 d-none d-lg-block">
          <div class="search-bar border rounded-2 px-3 border-dark-subtle">
            <form id="search-form" class="text-center d-flex align-items-center" action="{{ route('products.search') }}" method="GET">
              <input type="text" name="query" class="form-control border-0 bg-transparent"
                placeholder="Поиск товара" />
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                <path fill="currentColor"
                  d="M21.71 20.29L18 16.61A9 9 0 1 0 16.61 18l3.68 3.68a1 1 0 0 0 1.42 0a1 1 0 0 0 0-1.39ZM11 18a7 7 0 1 1 7-7a7 7 0 0 1-7 7Z" />
              </svg>
            </form>
          </div>
        </div>
      </div>
    </div>

    <div class="container-fluid">
      <hr class="m-0">
    </div>

    <div class="container">
      <nav class="main-menu d-flex navbar navbar-expand-lg ">
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar"
          aria-controls="offcanvasNavbar">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">

          <div class="offcanvas-header justify-content-center">
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
          </div>

          <div class="offcanvas-body justify-content-between">
            <ul class="navbar-nav menu-list list-unstyled d-flex gap-md-3 mb-0 justify-content-center flex-grow-1">
              @foreach ($categories as $category)
                <li class="nav-item">
                  <a href="/#{{Str::slug($category->name_category)}}" class="nav-link">{{$category->name_category}}</a>
                </li>
              @endforeach
            </ul>

            <div class="d-flex align-items-center">
              @if(Auth::check())
                <ul class="navbar-nav d-flex flex-row align-items-center mb-0">
                  <li class="nav-item">
                    <a class="nav-link me-3" href="{{ route('profile.show') }}">
                      <iconify-icon icon="mdi:account" class="fs-4"></iconify-icon>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link me-3" href="{{ route('cart.index') }}">
                      <iconify-icon icon="mdi:cart" class="fs-4"></iconify-icon>
                    </a>
                  </li>

                  <!-- Обновленное меню для разных ролей -->
                  @if(Auth::check() && Auth::user()->isAdmin())
                    <li class="nav-item dropdown">
                      <a class="nav-link me-3 dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                          Админ панель
                      </a>
                      <ul class="dropdown-menu" aria-labelledby="adminDropdown">
                          <li><a class="dropdown-item" href="{{ route('admin.products.index') }}">Управление товарами</a></li>
                          <li><a class="dropdown-item" href="{{ route('admin.categories.index') }}">Управление категориями</a></li>
                          <li><a class="dropdown-item" href="{{ route('admin.orders.index') }}">Управление заказами</a></li>
                          <li><hr class="dropdown-divider"></li>
                          <li><a class="dropdown-item" href="{{ route('admin.users.index') }}">Управление пользователями</a></li>
                          <li><a class="dropdown-item" href="{{ route('admin.ingredients.index') }}">Управление ингредиентами</a></li>
                          <li><a class="dropdown-item" href="{{ route('admin.schedules.index') }}">Графики работы</a></li>
                          <li><a class="dropdown-item" href="{{ route('admin.shifts.index') }}">История смен</a></li>
                          <li><hr class="dropdown-divider"></li>
                          <li><a class="dropdown-item" href="{{ route('admin.analytics.index') }}">Аналитика</a></li>
                          <li><a class="dropdown-item" href="{{ route('admin.reports.index') }}">Отчеты</a></li>
                      </ul>
                    </li>
                  @elseif(Auth::check() && Auth::user()->isManager())
                    <li class="nav-item dropdown">
                      <a class="nav-link me-3 dropdown-toggle" href="#" id="managerDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                          Панель менеджера
                      </a>
                      <ul class="dropdown-menu" aria-labelledby="managerDropdown">
                          <li><a class="dropdown-item" href="{{ route('manager.dashboard') }}">Главная</a></li>
                          <li><a class="dropdown-item" href="{{ route('manager.orders') }}">Управление заказами</a></li>
                          <li><a class="dropdown-item" href="{{ route('manager.ingredients') }}">Остатки ингредиентов</a></li>
                          <li><a class="dropdown-item" href="{{ route('manager.products.availability') }}">Наличие блюд</a></li>
                          <li><hr class="dropdown-divider"></li>
                          <li><a class="dropdown-item" href="{{ route('manager.shifts.index') }}">Мои смены</a></li>
                          <li><a class="dropdown-item" href="{{ route('employee.schedule.index') }}">Мой график</a></li>
                      </ul>
                    </li>
                  @endif

                  <li class="nav-item">
                    <a class="nav-link me-3" href="#" id="logoutLink" role="button" aria-expanded="false">
                        Выход
                    </a>
                  </li>
                </ul>
              @else
                <div class="d-flex align-items-center">
                    <a class="nav-link me-3" href="login">Вход</a>
                    <a class="nav-link me-3" href="register">Регистрация</a>
                    <a href="{{ route('cart.index') }}" class="nav-link">
                        <iconify-icon icon="mdi:cart" class="fs-4"></iconify-icon>
                    </a>
                </div>
              @endif
            </div>
          </div>
        </div>
      </nav>
    </div>
  </header>

  <!-- Added toast notification container -->
  <div class="toast-container">
    @if(session('success'))
      <div class="toast custom-toast show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="3000">
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
          {{ session('success') }}
        </div>
      </div>
    @endif
  </div>

  @yield('main_content')
  @yield('content')

  <div id="footer-bottom">
    <div class="container">
      <hr class="m-0">
      <div class="row mt-3">
        <div class="col-md-6 copyright">
          <p class="secondary-font">© 2025 Курсовая </p>
        </div>
      </div>
    </div>
  </div>

  <!-- ... existing scripts ... -->
  <script src="{{ asset('js/jquery-1.11.0.min.js') }}"></script>
  <script src="{{ asset('js/swiper.js') }}"></script>
  <script src="{{ asset('js/plugins.js') }}"></script>
  <script src="{{ asset('js/script.js') }}"></script>
  <script src="{{ asset('js/iconify.js') }}"></script>
  
  <script>
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация всех dropdown элементов
    var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
    var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl);
    });
    
    // Инициализация toast уведомлений
    var toastElList = [].slice.call(document.querySelectorAll('.toast'));
    var toastList = toastElList.map(function(toastEl) {
        var toast = new bootstrap.Toast(toastEl);
        toast.show();
        return toast;
    });
});

document.getElementById('logoutLink').addEventListener('click', function(e) {
    e.preventDefault();
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("logout") }}';
    var csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    form.appendChild(csrfToken);
    document.body.appendChild(form);
    form.submit();
});
</script>

</body>
</html>
