<!-- СТРАНИЦА Авторизации -->
@extends('layouts.app')

@section('title') Авторизация @endsection

@section('main_content')

<section id="login" style="background: #F9F3EC">
    <div class="container" style="margin-top: 180px">
      <div class="row my-5 py-5">
        <div class="offset-md-3 col-md-6 my-5 ">
          <h2 class="display-4 fw-normal text-center">Авторизуйся для <span class="text-primary">Оформления заказа</span>
          </h2>
          <form method="post" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
              <input type="tel" class="form-control form-control-lg" name="phone" id="phone"
                placeholder="Номер телефона">
            </div>
            <div class="mb-3">
              <input type="password" class="form-control form-control-lg" name="password" id="password"
                placeholder="Пароль">
            </div>
            <div class="d-grid gap-2">
              <button type="submit" class="btn btn-dark btn-lg rounded-1">Войти</button>
            </div>
          </form>
          <span class="rating secondary-font"><a href="#">Забыли пароль?</a></span> 
          @if($errors->any())
                    <div class="alert alert-danger mt-3">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                            <li>{{$error}}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif 
        </div>
      </div>
     
    </div>
    
  </section>
@endsection
<!-- <script>
    $(document).ready(function() {
        $('#togglePassword').click(function() {
            const passwordField = $('#password');
            const passwordFieldType = passwordField.attr('type');
            if (passwordFieldType === 'password') {
                passwordField.attr('type', 'text');
                $(this).find('i').removeClass('fa-eye-slash').addClass('fa-eye');
            } else {
                passwordField.attr('type', 'password');
                $(this).find('i').removeClass('fa-eye').addClass('fa-eye-slash');
            }
        });
    });
</script> -->