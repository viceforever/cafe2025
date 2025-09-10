@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h4>Редактировать ингредиент</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.ingredients.update', $ingredient) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Название ингредиента</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $ingredient->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="unit" class="form-label">Единица измерения</label>
                            <select class="form-select @error('unit') is-invalid @enderror" id="unit" name="unit" required>
                                <option value="">Выберите единицу</option>
                                <option value="кг" {{ old('unit', $ingredient->unit) === 'кг' ? 'selected' : '' }}>кг</option>
                                <option value="г" {{ old('unit', $ingredient->unit) === 'г' ? 'selected' : '' }}>г</option>
                                <option value="л" {{ old('unit', $ingredient->unit) === 'л' ? 'selected' : '' }}>л</option>
                                <option value="мл" {{ old('unit', $ingredient->unit) === 'мл' ? 'selected' : '' }}>мл</option>
                                <option value="шт" {{ old('unit', $ingredient->unit) === 'шт' ? 'selected' : '' }}>шт</option>
                            </select>
                            @error('unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="quantity" class="form-label">Текущий остаток</label>
                            <input type="number" step="0.01" class="form-control @error('quantity') is-invalid @enderror" 
                                   id="quantity" name="quantity" value="{{ old('quantity', $ingredient->quantity) }}" required>
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="min_quantity" class="form-label">Минимальный остаток</label>
                            <input type="number" step="0.01" class="form-control @error('min_quantity') is-invalid @enderror" 
                                   id="min_quantity" name="min_quantity" value="{{ old('min_quantity', $ingredient->min_quantity) }}" required>
                            @error('min_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="cost_per_unit" class="form-label">Стоимость за единицу (₽)</label>
                            <input type="number" step="0.01" class="form-control @error('cost_per_unit') is-invalid @enderror" 
                                   id="cost_per_unit" name="cost_per_unit" value="{{ old('cost_per_unit', $ingredient->cost_per_unit) }}" required>
                            @error('cost_per_unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.ingredients.index') }}" class="btn btn-secondary">Отмена</a>
                            <button type="submit" class="btn btn-primary">Обновить ингредиент</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
