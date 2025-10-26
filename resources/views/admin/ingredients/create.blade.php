@extends('layouts.app')

@section('content')
<div class="container" style="padding-top: 220px;">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h4>Добавить ингредиент</h4>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div style="background-color: #f8d7da; border: 1px solid #f5c2c7; border-radius: 8px; padding: 16px; margin-bottom: 20px;">
                            <ul style="margin: 0; padding-left: 20px; color: #842029;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.ingredients.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Название ингредиента</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="unit" class="form-label">Единица измерения</label>
                            <select class="form-select @error('unit') is-invalid @enderror" id="unit" name="unit" required>
                                <option value="">Выберите единицу</option>
                                <option value="кг" {{ old('unit') === 'кг' ? 'selected' : '' }}>кг</option>
                                <option value="г" {{ old('unit') === 'г' ? 'selected' : '' }}>г</option>
                                <option value="л" {{ old('unit') === 'л' ? 'selected' : '' }}>л</option>
                                <option value="мл" {{ old('unit') === 'мл' ? 'selected' : '' }}>мл</option>
                                <option value="шт" {{ old('unit') === 'шт' ? 'selected' : '' }}>шт</option>
                            </select>
                            @error('unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="quantity" class="form-label">Текущий остаток</label>
                            <input type="number" step="0.01" class="form-control @error('quantity') is-invalid @enderror" 
                                   id="quantity" name="quantity" value="{{ old('quantity', 0) }}" required>
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="min_quantity" class="form-label">Минимальный остаток</label>
                            <input type="number" step="0.01" class="form-control @error('min_quantity') is-invalid @enderror" 
                                   id="min_quantity" name="min_quantity" value="{{ old('min_quantity', 0) }}" required>
                            @error('min_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="cost_per_unit" class="form-label">Стоимость за единицу (₽)</label>
                            <input type="number" step="0.01" class="form-control @error('cost_per_unit') is-invalid @enderror" 
                                   id="cost_per_unit" name="cost_per_unit" value="{{ old('cost_per_unit', 0) }}" required>
                            @error('cost_per_unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.ingredients.index') }}" class="btn btn-secondary">Отмена</a>
                            <button type="submit" class="btn btn-primary">Добавить ингредиент</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
