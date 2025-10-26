@if ($paginatedCart->count() > 0)
    @foreach ($paginatedCart as $id => $item)
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
