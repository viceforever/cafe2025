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

        <button type="submit" class="btn btn-primary">Создать товар</button>
    </form>
</div>

<script>
let ingredientIndex = 0;
let availableIngredients = [];

// Загружаем список ингредиентов
fetch('{{ route("admin.ingredients.get-all") }}')
    .then(response => response.json())
    .then(data => {
        availableIngredients = data;
    });

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
            <button type="button" class="btn btn-outline-danger remove-ingredient">
                <i class="iconify" data-icon="mdi:delete"></i>
            </button>
        </div>
    `;
    
    container.appendChild(ingredientRow);
    
    // Добавляем обработчик для удаления
    ingredientRow.querySelector('.remove-ingredient').addEventListener('click', function() {
        ingredientRow.remove();
    });
    
    // Добавляем валидацию количества
    const quantityInput = ingredientRow.querySelector('.quantity-input');
    const ingredientSelect = ingredientRow.querySelector('.ingredient-select');
    
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
    
    ingredientIndex++;
});
</script>
@endsection
