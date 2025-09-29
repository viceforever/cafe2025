@extends('layouts.app')

@section('content')
<div class="container" style="padding-top: 220px;">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Наличие блюд</h2>
                <a href="{{ route('manager.dashboard') }}" class="btn btn-secondary">
                    <i class="iconify" data-icon="mdi:arrow-left"></i> Назад к панели
                </a>
            </div>

            <div class="row">
                @foreach($availability as $item)
                    <div class="col-md-6 mb-4">
                        <div class="card {{ $item['available'] ? 'border-success' : 'border-danger' }}">
                            <div class="card-header {{ $item['available'] ? 'bg-primary text-white' : 'bg-primary text-white' }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">{{ $item['product']->name_product }}</h6>
                                    <span class="badge {{ $item['available'] ? 'bg-success text-white' : 'bg-danger text-white' }}">
                                        {{ $item['available'] ? 'Доступно' : 'Недоступно' }}
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <h6 class="mb-3">Необходимые ингредиенты:</h6>
                                <div class="row">
                                    @foreach($item['all_ingredients'] as $ingredientData)
                                        <div class="col-md-6 mb-2">
                                            <div class="d-flex justify-content-between align-items-center p-2 rounded {{ $ingredientData['sufficient'] ? 'bg-light-success' : 'bg-light-danger' }}" style="background-color: {{ $ingredientData['sufficient'] ? '#d4edda' : '#f8d7da' }};">
                                                <div>
                                                    <strong>{{ $ingredientData['ingredient']->name }}</strong><br>
                                                    <small class="text-muted">
                                                        Нужно: {{ $ingredientData['needed_quantity'] }} {{ $ingredientData['unit'] }}
                                                    </small>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge {{ $ingredientData['sufficient'] ? 'bg-success' : 'bg-danger' }}">
                                                        {{ $ingredientData['available_quantity'] }} {{ $ingredientData['unit'] }}
                                                    </span>
                                                    @if($ingredientData['sufficient'])
                                                        <i class="iconify text-success d-block" data-icon="mdi:check-circle"></i>
                                                    @else
                                                        <i class="iconify text-danger d-block" data-icon="mdi:alert-circle"></i>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                @if(!$item['available'] && count($item['missing_ingredients']) > 0)
                                    <hr>
                                    <h6 class="text-dark mb-0 mt-3">Недостающие ингредиенты:</h6>
                                    <ul class="list-unstyled">
                                        @foreach($item['missing_ingredients'] as $ingredient)
                                            <li class="text-dark mb-0 mt-3">
                                                <i class="iconify" data-icon="mdi:alert-circle"></i>
                                                {{ $ingredient->name }} 
                                                (остаток: {{ $ingredient->quantity }} {{ $ingredient->unit }})
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <hr>
                                    <p class="text-dark mb-0 mt-3">
                                        <i class="iconify text-success" data-icon="mdi:check-circle"></i>
                                        Все ингредиенты в наличии
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
