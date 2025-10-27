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
            {{-- Удалён блок с session('success') --}}

            <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" id="product-form">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="name_product" class="form-label">Название товара</label>
                    <input type="text" class="form-control" id="name_product" name="name_product" value="{{ $product->name_product }}" required>
                </div>
                <div class="mb-3">
                    <label for="description_product" class="form-label">Описание товара</label>
                    <textarea class="form-control" id="description_product" name="description_product" rows="3" maxlength="300" required>{{ $product->description_product }}</textarea>
                    <small class="form-text text-muted">
                        <span id="char-count">{{ strlen($product->description_product) }}</span>/300 символов
                    </small>
                </div>
                <div class="mb-3">
                    <label for="price_product" class="form-label">Цена товара</label>
                    <input type="number" class="form-control" id="price_product" name="price_product" step="0.01" min="0" max="999999.99" value="{{ $product->price_product }}" required>
                    <small class="form-text text-muted">
                        Максимальная цена: 999 999.99
                    </small>
                    <div id="price-error" class="mt-1" style="display: none; color: #dc3545; font-weight: bold;"></div>
                    <div id="price-warning" class="mt-1" style="display: none; color: #ffc107; font-weight: bold;"></div>
                </div>
                <div class="mb-3">
                    <label for="img_product" class="form-label">Изображение товара</label>
                    <input type="file" class="form-control" id="img_product" name="img_product" accept="image/jpeg,image/png,image/jpg,image/gif">
                    <small class="form-text text-muted">
                        Оставьте пустым, если не хотите менять изображение. Максимальный размер: 4 МБ. Форматы: JPEG, PNG, JPG, GIF
                    </small>
                    <div id="file-size-error" class="mt-1" style="display: none; color: #000;"></div>
                    <div id="file-size-info" class="text-muted mt-1" style="display: none;"></div>
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

                <button type="submit" class="btn btn-primary" id="submit-btn">Обновить товар</button>
            </form>
        </div>
    </div>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {
    let ingredientIndex = {{ $product->ingredients ? $product->ingredients->count() : 1 }};
    
    document.getElementById('description_product').addEventListener('input', function() {
        document.getElementById('char-count').textContent = this.value.length;
    });
    
    const priceInput = document.getElementById('price_product');
    const priceError = document.getElementById('price-error');
    const priceWarning = document.getElementById('price-warning');
    const submitBtn = document.getElementById('submit-btn');
    const MAX_PRICE = 999999.99;
    const WARNING_THRESHOLD = 900000;
    
    priceInput.addEventListener('input', function() {
        const price = parseFloat(this.value);
        
        if (isNaN(price)) {
            priceError.style.display = 'none';
            priceWarning.style.display = 'none';
            return;
        }
        
        if (price > MAX_PRICE) {
            priceError.textContent = `❌ Цена превышает максимально допустимое значение (999 999.99)! Товар не может быть обновлен с такой ценой.`;
            priceError.style.display = 'block';
            priceWarning.style.display = 'none';
            submitBtn.disabled = true;
            submitBtn.title = 'Уменьшите цену товара';
            this.classList.add('is-invalid');
        } else if (price >= WARNING_THRESHOLD) {
            priceError.style.display = 'none';
            priceWarning.textContent = `⚠️ Внимание: цена приближается к максимальному значению (999 999.99)`;
            priceWarning.style.display = 'block';
            submitBtn.disabled = false;
            submitBtn.title = '';
            this.classList.remove('is-invalid');
        } else if (price < 0) {
            priceError.textContent = `❌ Цена не может быть отрицательной!`;
            priceError.style.display = 'block';
            priceWarning.style.display = 'none';
            submitBtn.disabled = true;
            submitBtn.title = 'Введите положительную цену';
            this.classList.add('is-invalid');
        } else {
            priceError.style.display = 'none';
            priceWarning.style.display = 'none';
            submitBtn.disabled = false;
            submitBtn.title = '';
            this.classList.remove('is-invalid');
        }
    });
    
    document.getElementById('img_product').addEventListener('change', function() {
        const fileInput = this;
        const file = fileInput.files[0];
        const errorDiv = document.getElementById('file-size-error');
        const infoDiv = document.getElementById('file-size-info');
        const submitBtn = document.getElementById('submit-btn');
        const maxSize = 4 * 1024 * 1024; // 4 МБ в байтах
        
        if (file) {
            const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
            
            if (file.size > maxSize) {
                errorDiv.textContent = `❌ Размер файла (${fileSizeMB} МБ) превышает максимально допустимый размер (4 МБ). Пожалуйста, выберите файл меньшего размера.`;
                errorDiv.style.display = 'block';
                infoDiv.style.display = 'none';
                submitBtn.disabled = true; // Блокируем кнопку отправки
                submitBtn.title = 'Выберите файл меньшего размера';
            } else {
                errorDiv.style.display = 'none';
                infoDiv.textContent = `✓ Размер файла: ${fileSizeMB} МБ`;
                infoDiv.style.display = 'block';
                infoDiv.className = 'text-success mt-1';
                submitBtn.disabled = false;
                submitBtn.title = '';
            }
        } else {
            errorDiv.style.display = 'none';
            infoDiv.style.display = 'none';
            submitBtn.disabled = false;
            submitBtn.title = '';
        }
    });
    
    document.getElementById('product-form').addEventListener('submit', function(e) {
        const fileInput = document.getElementById('img_product');
        const file = fileInput.files[0];
        const maxSize = 4 * 1024 * 1024; // 4 МБ
        
        if (file && file.size > maxSize) {
            e.preventDefault();
            e.stopPropagation();
            const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
            alert(`❌ Невозможно отправить форму!\n\nРазмер файла (${fileSizeMB} МБ) превышает максимально допустимый размер (4 МБ).\n\nПожалуйста, выберите файл меньшего размера.`);
            fileInput.focus();
            return false;
        }
        
        const price = parseFloat(priceInput.value);
        if (price > MAX_PRICE) {
            e.preventDefault();
            e.stopPropagation();
            alert(`❌ Невозможно обновить товар!\n\nЦена (${price.toFixed(2)}) превышает максимально допустимое значение (999 999.99).\n\nПожалуйста, уменьшите цену товара.`);
            priceInput.focus();
            return false;
        }
        
        if (price < 0) {
            e.preventDefault();
            e.stopPropagation();
            alert(`❌ Невозможно обновить товар!\n\nЦена не может быть отрицательной.`);
            priceInput.focus();
            return false;
        }
    });
    
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
