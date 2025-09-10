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
                    <tbody>
                        @if (session('cart') && count(session('cart')) > 0)
                            @foreach (session('cart') as $id => $item)
                                <tr>
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
                                        <div class="input-group product-qty align-items-center w-50">
                                            <form action="{{ route('cart.update', $id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="action" value="decrease">
                                                <button type="submit" class="btn btn-light btn-number">
                                                    <svg width="16" height="16">
                                                        <use xlink:href="#minus"></use>
                                                    </svg>
                                                </button>
                                            </form>

                                            <input type="text" id="quantity-{{ $id }}" name="quantity" class="form-control input-number text-center p-2 mx-1" value="{{ $item['quantity'] }}" readonly>
                                            
                                            <form action="{{ route('cart.update', $id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="action" value="increase">
                                                <button type="submit" class="btn btn-light btn-number">
                                                    <svg width="16" height="16">
                                                        <use xlink:href="#plus"></use>
                                                    </svg>
                                                </button>
                                            </form> 
                                        </div>
                                    </td>
                                    <td class="py-4 align-middle">
                                        <div class="total-price">
                                            <span class="secondary-font fw-medium">{{ $item['price'] * $item['quantity'] }} ₽</span>
                                        </div>
                                    </td>
                                    <td class="py-4 align-middle">
                                        <div class="cart-remove">
                                            <form action="{{ route('cart.remove', $id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-link p-0">
                                                    <svg width="24" height="24">
                                                        <use xlink:href="#trash"></use>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
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
                        <table cellspacing="0" class="table text-uppercase">
                            <tbody>
                                <tr class="subtotal pt-2 pb-2 border-top border-bottom">
                                    <td data-title="Subtotal">
                                        <span class="price-amount amount text-dark ps-5">
                                            <bdi>
                                                <span class="price-currency-symbol">₽</span>
                                                <span id="cart-total">
                                                    {{ array_reduce(session('cart', []), function ($carry, $item) {
                                                        return $carry + ($item['price'] * $item['quantity']);
                                                    }, 0) }}
                                                </span>
                                            </bdi>
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div>
                        <a href="{{ route('checkout') }}" class="btn btn-primary">Оформить заказ</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection