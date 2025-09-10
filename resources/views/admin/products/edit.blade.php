@extends('layouts.app')

@section('title', 'Редактировать товар')

@section('main_content')
<div class="container min-vh-100 d-flex flex-column">
    <div class="row flex-grow-1" style="margin-top: 200px; margin-bottom: 50px;">
        <div class="col-12">
            <h1>Редактировать товар</h1>
            @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="name_product" class="form-label">Название товара</label>
                    <input type="text" class="form-control" id="name_product" name="name_product" value="{{ $product->name_product }}" required>
                </div>
                <div class="mb-3">
                    <label for="description_product" class="form-label">Описание товара</label>
                    <textarea class="form-control" id="description_product" name="description_product" rows="3" required>{{ $product->description_product }}</textarea>
                </div>
                <div class="mb-3">
                    <label for="price_product" class="form-label">Цена товара</label>
                    <input type="number" class="form-control" id="price_product" name="price_product" step="0.01" value="{{ $product->price_product }}" required>
                </div>
                <div class="mb-3">
                    <label for="img_product" class="form-label">Изображение товара</label>
                    <input type="file" class="form-control" id="img_product" name="img_product">
                    <small class="form-text text-muted">Оставьте пустым, если не хотите менять изображение</small>
                </div>
                <div class="mb-3">
                    <label for="id_category" class="form-label">Категория товара</label>
                    <select class="form-control" id="id_category" name="id_category" required>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ $product->id_category == $category->id ? 'selected' : '' }}>
                                {{ $category->name_category }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Обновить товар</button>
            </form>
        </div>
    </div>
</div>
@endsection