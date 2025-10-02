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

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" id="product-form">
        @csrf
        <div class="mb-3">
            <label for="name_product" class="form-label">Название товара</label>
            <input type="text" class="form-control" id="name_product" name="name_product" required>
        </div>
        <div class="mb-3">
            <label for="description_product" class="form-label">Описание товара</label>
            <textarea class="form-control" id="description_product" name="description_product" rows="3" maxlength="300" required></textarea>
            <small class="form-text text-muted">
                <span id="char-count">0</span>/300 символов
            </small>
        </div>
        <div class="mb-3">
            <label for="price_product" class="form-label">Цена товара</label>
            <input type="number" class="form-control" id="price_product" name="price_product" step="0.01" required>
        </div>
        <div class="mb-3">
            <label for="img_product" class="form-label">Изображение товара</label>
            <input type="file" class="form-control" id="img_product" name="img_product" accept="image/jpeg,image/png,image/jpg,image/gif" required>
            <!-- Изменен максимальный размер с 10 МБ на 4 МБ -->
            <small class="form-text text-muted">
                Максимальный размер файла: 4 МБ. Допустимые форматы: JPEG, PNG, JPG, GIF
            </small>
            <div id="file-size-error" class="mt-1" style="display: none; color: #000;"></div>
            <!-- Добавлена информация о размере выбранного файла -->
            <div id="file-size-info" class="text-muted mt-1" style="display: none;"></div>
        </div>
        <div class="mb-3">
            <label for="id_category" class="form-label">Категория товара</label>
            <select class="form-control" id="id_category" name="id_category" required>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name_category }}</option>
                @endforeach
            </select>
        </div>

        <!-- добавляем секцию выбора ингредиентов -->
        <div class="mb-3">
            <label class="form-label">Ингредиенты</label>
            <div class="card">
                <div class="card-body">
                    <div id="ingredients-container">
                        <!-- Ингредиенты будут добавляться здесь -->
                    </div>
                    <button type="button" class="btn btn-outline-primary" id="add-ingredient">
                        <i class="iconify" data-icon="mdi:plus"></i> Добавить ингредиент
                    </button>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" id="submit-btn">Создать товар</button>
    </form>
</div>

<script>
let ingredientIndex = 0;
let availableIngredients = [];
let selectedIngredients = [];

fetch('{{ route("admin.ingredients.get-all") }}')
    .then(response => response.json())
    .then(data => {
        availableIngredients = data;
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
});

function updateAvailableIngredients() {
    document.querySelectorAll('.ingredient-select').forEach(select => {
        const currentValue = select.value;
        const options = select.querySelectorAll('option');
        
        options.forEach(option => {
            if (option.value === '') return;
            
            if (selectedIngredients.includes(option.value) && option.value !== currentValue) {
                option.style.display = 'none';
                option.disabled = true;
            } else {
                option.style.display = 'block';
                option.disabled = false;
            }
        });
    });
}

document.getElementById('add-ingredient').addEventListener('click', function() {
    const container = document.getElementById('ingredients-container');
    const ingredientRow = document.createElement('div');
    ingredientRow.className = 'row mb-2 ingredient-row';
    ingredientRow.innerHTML = `
        <div class="col-md-6">
            <select class="form-control ingredient-select" name="ingredients[${ingredientIndex}][id]" required>
                <option value="">Выберите ингредиент</option>
                ${availableIngredients.map(ing => 
                    `<option value="${ing.id}" data-unit="${ing.unit}" data-available="${ing.quantity}">
                        ${ing.name} (доступно: ${ing.quantity} ${ing.unit})
                    </option>`
                ).join('')}
            </select>
        </div>
        <div class="col-md-4">
            <input type="number" step="0.01" class="form-control quantity-input" 
                   name="ingredients[${ingredientIndex}][quantity]" 
                   placeholder="Количество" required>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-danger remove-ingredient">
                Удалить
            </button>
        </div>
    `;
    
    container.appendChild(ingredientRow);
    
    const ingredientSelect = ingredientRow.querySelector('.ingredient-select');
    const quantityInput = ingredientRow.querySelector('.quantity-input');
    
    ingredientSelect.addEventListener('change', function() {
        const oldValue = this.dataset.oldValue;
        const newValue = this.value;
        
        if (oldValue) {
            const index = selectedIngredients.indexOf(oldValue);
            if (index > -1) {
                selectedIngredients.splice(index, 1);
            }
        }
        
        if (newValue) {
            selectedIngredients.push(newValue);
        }
        
        this.dataset.oldValue = newValue;
        updateAvailableIngredients();
    });
    
    ingredientRow.querySelector('.remove-ingredient').addEventListener('click', function() {
        const selectValue = ingredientSelect.value;
        if (selectValue) {
            const index = selectedIngredients.indexOf(selectValue);
            if (index > -1) {
                selectedIngredients.splice(index, 1);
            }
        }
        ingredientRow.remove();
        updateAvailableIngredients();
    });
    
    quantityInput.addEventListener('input', function() {
        const selectedOption = ingredientSelect.selectedOptions[0];
        if (selectedOption) {
            const available = parseFloat(selectedOption.dataset.available);
            const needed = parseFloat(this.value);
            
            if (needed > available) {
                this.setCustomValidity(`Недостаточно ингредиента. Доступно: ${available} ${selectedOption.dataset.unit}`);
            } else {
                this.setCustomValidity('');
            }
        }
    });
    
    updateAvailableIngredients();
    
    ingredientIndex++;
});

document.getElementById('description_product').addEventListener('input', function() {
    document.getElementById('char-count').textContent = this.value.length;
});
</script>
@endsection
