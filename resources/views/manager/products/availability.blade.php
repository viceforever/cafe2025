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
                            <div class="card-header {{ $item['available'] ? 'bg-success text-white' : 'bg-danger text-white' }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">{{ $item['product']->name_product }}</h6>
                                    <span class="badge {{ $item['available'] ? 'bg-light text-success' : 'bg-light text-danger' }}">
                                        {{ $item['available'] ? 'Доступно' : 'Недоступно' }}
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                @if(!$item['available'] && count($item['missing_ingredients']) > 0)
                                    <h6 class="text-danger">Недостающие ингредиенты:</h6>
                                    <ul class="list-unstyled">
                                        @foreach($item['missing_ingredients'] as $ingredient)
                                            <li class="text-danger">
                                                <i class="iconify" data-icon="mdi:alert-circle"></i>
                                                {{ $ingredient->name }} 
                                                (остаток: {{ $ingredient->quantity }} {{ $ingredient->unit }})
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-success mb-0">
                                        <i class="iconify" data-icon="mdi:check-circle"></i>
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
