@extends('layouts.app')

@section('title', 'Создать новый товар')

@section('main_content')
<div class="container" style="margin-top: 220px; margin-bottom: 50px;">
    <h1>Создать новый товар</h1>
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="name_product" class="form-label">Название товара</label>
            <input type="text" class="form-control" id="name_product" name="name_product" required>
        </div>
        <div class="mb-3">
            <label for="description_product" class="form-label">Описание товара</label>
            <textarea class="form-control" id="description_product" name="description_product" rows="3" required></textarea>
        </div>
        <div class="mb-3">
            <label for="price_product" class="form-label">Цена товара</label>
            <input type="number" class="form-control" id="price_product" name="price_product" step="0.01" required>
        </div>
        <div class="mb-3">
            <label for="img_product" class="form-label">Изображение товара</label>
            <input type="file" class="form-control" id="img_product" name="img_product" required>
        </div>
        <div class="mb-3">
            <label for="id_category" class="form-label">Категория товара</label>
            <select class="form-control" id="id_category" name="id_category" required>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name_category }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Создать товар</button>
    </form>
</div>
@endsection