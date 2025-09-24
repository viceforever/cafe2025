@extends('layouts.app')

@section('content')
<div class="container" style="padding-top: 220px;">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Отчеты</h4>
                </div>
                <div class="card-body">
                    <!-- Добавил статистические карточки для быстрого обзора -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Заказы сегодня</h6>
                                            <h3>{{ $todayOrders ?? 0 }}</h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-shopping-cart fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Выручка за месяц</h6>
                                            <h3>{{ number_format($monthlyRevenue ?? 0, 0, ',', ' ') }} ₽</h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-ruble-sign fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Мало ингредиентов</h6>
                                            <h3>{{ $lowStockIngredients ?? 0 }}</h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-chart-line"></i> Отчет по продажам
                                    </h5>
                                    <p class="card-text">
                                        Детальный анализ продаж с возможностью фильтрации по датам, категориям и товарам.
                                        Включает статистику по заказам, выручке и популярным товарам.
                                    </p>
                                    <a href="{{ route('admin.reports.sales') }}" class="btn btn-primary">
                                        Открыть отчет
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-boxes"></i> Отчет по ингредиентам
                                    </h5>
                                    <p class="card-text">
                                        Анализ использования ингредиентов, контроль остатков и расчет затрат.
                                        Помогает оптимизировать закупки и избежать дефицита.
                                    </p>
                                    <a href="{{ route('admin.reports.ingredients') }}" class="btn btn-primary">
                                        Открыть отчет
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
