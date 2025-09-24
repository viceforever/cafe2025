@extends('layouts.app')

@section('title', 'Редактировать товар')

@section('main_content')
<div class="container min-vh-100 d-flex flex-column">
    <div class="row flex-grow-1" style="margin-top: 220px; margin-bottom: 50px;">
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

                <!-- восстанавливаем возможность редактирования ингредиентов -->
                <div class="mb-3">
                    <label class="form-label">Ингредиенты</label>
                    <div id="ingredients-container">
                        @if($product->ingredients && $product->ingredients->count() > 0)
                            @foreach($product->ingredients as $index => $ingredient)
                            <div class="ingredient-row mb-2">
                                <div class="row">
                                    <div class="col-md-6">
                                        <select class="form-control ingredient-select" name="ingredients[{{ $index }}][id]" required>
                                            <option value="">Выберите ингредиент</option>
                                            @foreach ($ingredients as $ing)
                                                <option value="{{ $ing->id }}" {{ $ingredient->id == $ing->id ? 'selected' : '' }}>
                                                    {{ $ing->name }} ({{ $ing->quantity }} {{ $ing->unit }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="number" class="form-control" name="ingredients[{{ $index }}][quantity]" 
                                               placeholder="Количество" step="0.01" min="0.01" 
                                               value="{{ $ingredient->pivot->quantity_needed }}" required>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-danger remove-ingredient">Удалить</button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="ingredient-row mb-2">
                                <div class="row">
                                    <div class="col-md-6">
                                        <select class="form-control ingredient-select" name="ingredients[0][id]" required>
                                            <option value="">Выберите ингредиент</option>
                                            @foreach ($ingredients as $ingredient)
                                                <option value="{{ $ingredient->id }}">
                                                    {{ $ingredient->name }} ({{ $ingredient->quantity }} {{ $ingredient->unit }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="number" class="form-control" name="ingredients[0][quantity]" 
                                               placeholder="Количество" step="0.01" min="0.01" required>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-danger remove-ingredient">Удалить</button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    <button type="button" class="btn btn-secondary" id="add-ingredient">Добавить ингредиент</button>
                </div>

                <button type="submit" class="btn btn-primary">Обновить товар</button>
            </form>
        </div>
    </div>
</div>
@endsection

<!-- добавляем JavaScript для управления ингредиентами с защитой от дублирования -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    let ingredientIndex = {{ $product->ingredients ? $product->ingredients->count() : 1 }};
    
    function updateAvailableIngredients() {
        const selectedIngredients = [];
        document.querySelectorAll('.ingredient-select').forEach(select => {
            if (select.value) {
                selectedIngredients.push(select.value);
            }
        });
        
        document.querySelectorAll('.ingredient-select').forEach(select => {
            const currentValue = select.value;
            const options = select.querySelectorAll('option');
            
            options.forEach(option => {
                if (option.value === '') return;
                
                if (selectedIngredients.includes(option.value) && option.value !== currentValue) {
                    option.style.display = 'none';
                } else {
                    option.style.display = 'block';
                }
            });
        });
    }
    
    document.getElementById('add-ingredient').addEventListener('click', function() {
        const container = document.getElementById('ingredients-container');
        const newRow = document.createElement('div');
        newRow.className = 'ingredient-row mb-2';
        newRow.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <select class="form-control ingredient-select" name="ingredients[${ingredientIndex}][id]" required>
                        <option value="">Выберите ингредиент</option>
                        @foreach ($ingredients as $ingredient)
                            <option value="{{ $ingredient->id }}">
                                {{ $ingredient->name }} ({{ $ingredient->quantity }} {{ $ingredient->unit }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="number" class="form-control" name="ingredients[${ingredientIndex}][quantity]" 
                           placeholder="Количество" step="0.01" min="0.01" required>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger remove-ingredient">Удалить</button>
                </div>
            </div>
        `;
        container.appendChild(newRow);
        ingredientIndex++;
        updateAvailableIngredients();
    });
    
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-ingredient')) {
            e.target.closest('.ingredient-row').remove();
            updateAvailableIngredients();
        }
    });
    
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('ingredient-select')) {
            updateAvailableIngredients();
        }
    });
    
    updateAvailableIngredients();
});
</script>
