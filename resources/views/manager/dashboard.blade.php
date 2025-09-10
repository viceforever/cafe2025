@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Панель менеджера</h2>
                <div>
                    @if($activeShift)
                        <span class="badge bg-success me-2">Смена активна с {{ $activeShift->start_time->format('H:i') }}</span>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#endShiftModal">
                            Завершить смену
                        </button>
                    @else
                        <form action="{{ route('manager.shift.start') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success">Начать смену</button>
                        </form>
                    @endif
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <!-- Статистика -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4>{{ $todayOrders }}</h4>
                                    <p class="mb-0">Заказов сегодня</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="iconify" data-icon="mdi:receipt" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4>{{ number_format($todayRevenue, 0) }} ₽</h4>
                                    <p class="mb-0">Выручка сегодня</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="iconify" data-icon="mdi:cash" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4>{{ $pendingOrders }}</h4>
                                    <p class="mb-0">В обработке</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="iconify" data-icon="mdi:clock" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4>{{ $lowStockIngredients }}</h4>
                                    <p class="mb-0">Низкие остатки</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="iconify" data-icon="mdi:alert" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Быстрые действия -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="iconify text-primary mb-3" data-icon="mdi:receipt-text" style="font-size: 3rem;"></i>
                            <h5>Управление заказами</h5>
                            <p class="text-muted">Просмотр и изменение статусов заказов</p>
                            <a href="{{ route('manager.orders') }}" class="btn btn-primary">Перейти</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="iconify text-success mb-3" data-icon="mdi:food" style="font-size: 3rem;"></i>
                            <h5>Остатки ингредиентов</h5>
                            <p class="text-muted">Проверка наличия ингредиентов</p>
                            <a href="{{ route('manager.ingredients') }}" class="btn btn-success">Перейти</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="iconify text-info mb-3" data-icon="mdi:chart-line" style="font-size: 3rem;"></i>
                            <h5>Отчеты по сменам</h5>
                            <p class="text-muted">История и статистика смен</p>
                            <a href="{{ route('manager.shifts.index') }}" class="btn btn-info">Перейти</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно завершения смены -->
@if($activeShift)
<div class="modal fade" id="endShiftModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('manager.shift.end') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Завершить смену</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="notes" class="form-label">Комментарии к смене (необязательно)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" 
                                  placeholder="Особенности смены, проблемы, замечания..."></textarea>
                    </div>
                    <div class="alert alert-info">
                        <strong>Время смены:</strong> {{ $activeShift->start_time->format('H:i') }} - {{ now()->format('H:i') }}
                        ({{ $activeShift->start_time->diffForHumans(now(), true) }})
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-danger">Завершить смену</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
