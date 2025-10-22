@extends('layouts.app')

@section('content')
<div class="container" style="padding-top: 220px;">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Панель менеджера</h2>
                <div>
                    @if($activeShift)
                        <span class="badge bg-success me-2">Смена активна с {{ date('H:i', strtotime($activeShift->start_time)) }}</span>
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

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <!-- Статистика завершенной смены -->
            @if(session('completed_shift'))
                <div class="alert alert-info">
                    <h5>Смена завершена!</h5>
                    <p><strong>Время начала:</strong> <span id="completed-start-time" data-utc="{{ session('completed_shift')['start_time'] }}">{{ session('completed_shift')['start_time_display'] }}</span></p>
                    <p><strong>Время окончания:</strong> <span id="completed-end-time" data-utc="{{ session('completed_shift')['end_time'] }}">{{ session('completed_shift')['end_time_display'] }}</span></p>
                    <p><strong>Длительность:</strong> {{ session('completed_shift')['duration'] }}</p>
                </div>
            @endif

            <!-- Статистика активной смены -->
            @if($activeShift && $shiftStats)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-success">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="iconify me-2" data-icon="mdi:clock-check"></i>
                                Статистика активной смены
                            </h5>
                            <small>Начата в {{ date('H:i', strtotime($activeShift->start_time)) }}</small>
                        </div>
                        <div class="card-body">
                            <div class="row" id="shift-stats">
                                <div class="col-md-2">
                                    <div class="text-center">
                                        <h4 class="text-primary mb-1" id="shift-orders">{{ $shiftStats['orders_count'] }}</h4>
                                        <small class="text-muted">Заказов</small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center">
                                        <h4 class="text-primary mb-1" id="shift-revenue">{{ number_format($shiftStats['total_revenue'], 0) }} ₽</h4>
                                        <small class="text-muted">Общая выручка</small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center">
                                        <h4 class="text-primary mb-1" id="shift-cash">{{ number_format($shiftStats['cash_revenue'], 0) }} ₽</h4>
                                        <small class="text-muted">Наличными</small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center">
                                        <h4 class="text-primary mb-1" id="shift-card">{{ number_format($shiftStats['card_revenue'], 0) }} ₽</h4>
                                        <small class="text-muted">Картой</small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center">
                                        <h4 class="text-primary mb-1" id="shift-completed">{{ $shiftStats['completed_orders'] }}</h4>
                                        <small class="text-muted">Выполнено</small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center">
                                        <h4 class="text-primary mb-1" id="shift-pending">{{ $shiftStats['pending_orders'] }}</h4>
                                        <small class="text-muted">В работе</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
            <form action="{{ route('manager.shift.end') }}" method="POST" id="endShiftForm">
                @csrf
                <!-- Добавляем скрытые поля для передачи времен -->
                <input type="hidden" name="start_time" id="hiddenStartTime">
                <input type="hidden" name="end_time" id="hiddenEndTime">
                <input type="hidden" name="duration_minutes" id="hiddenDurationMinutes">
                
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
                    <div class="alert alert-info" id="shiftTimeInfo">
                        <!-- Время будет рассчитываться JavaScript -->
                        <strong>Время смены:</strong> <span id="shiftTimeDisplay">Загрузка...</span>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const endShiftModal = document.getElementById('endShiftModal');
    const shiftStartTime = new Date('{{ $activeShift ? date('c', strtotime($activeShift->start_time)) : '' }}');
    
    if (endShiftModal) {
        endShiftModal.addEventListener('show.bs.modal', function() {
            const now = new Date();
            
            // Рассчитываем длительность в минутах
            const durationMs = now.getTime() - shiftStartTime.getTime();
            const durationMinutes = Math.floor(durationMs / (1000 * 60));
            
            // Форматируем время для отображения
            const startTimeStr = shiftStartTime.toLocaleTimeString('ru-RU', {
                hour: '2-digit',
                minute: '2-digit',
                timeZone: 'Asia/Irkutsk'
            });
            
            const endTimeStr = now.toLocaleTimeString('ru-RU', {
                hour: '2-digit',
                minute: '2-digit',
                timeZone: 'Asia/Irkutsk'
            });
            
            // Форматируем длительность
            const hours = Math.floor(durationMinutes / 60);
            const minutes = durationMinutes % 60;
            let durationStr = '';
            if (hours > 0) {
                durationStr = hours + ' ч ' + minutes + ' мин';
            } else {
                durationStr = minutes + ' мин';
            }
            
            // Обновляем отображение
            document.getElementById('shiftTimeDisplay').textContent = 
                startTimeStr + ' - ' + endTimeStr + ' (' + durationStr + ')';
            
            // Заполняем скрытые поля для отправки на сервер
            document.getElementById('hiddenStartTime').value = shiftStartTime.toISOString();
            document.getElementById('hiddenEndTime').value = now.toISOString();
            document.getElementById('hiddenDurationMinutes').value = durationMinutes;
        });
    }

    function convertCompletedShiftTime() {
        const startTimeElement = document.getElementById('completed-start-time');
        if (startTimeElement && startTimeElement.dataset.utc) {
            const utcTime = new Date(startTimeElement.dataset.utc);
            startTimeElement.textContent = utcTime.toLocaleTimeString('ru-RU', {
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        const endTimeElement = document.getElementById('completed-end-time');
        if (endTimeElement && endTimeElement.dataset.utc) {
            const utcTime = new Date(endTimeElement.dataset.utc);
            endTimeElement.textContent = utcTime.toLocaleTimeString('ru-RU', {
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    }

    convertCompletedShiftTime();

    // JavaScript для автообновления статистики смены
    function updateShiftStats() {
        fetch('{{ route("manager.shift.stats") }}')
            .then(response => response.json())
            .then(data => {
                if (!data.error) {
                    document.getElementById('shift-orders').textContent = data.orders_count;
                    document.getElementById('shift-revenue').textContent = new Intl.NumberFormat('ru-RU').format(data.total_revenue) + ' ₽';
                    document.getElementById('shift-cash').textContent = new Intl.NumberFormat('ru-RU').format(data.cash_revenue) + ' ₽';
                    document.getElementById('shift-card').textContent = new Intl.NumberFormat('ru-RU').format(data.card_revenue) + ' ₽';
                    document.getElementById('shift-completed').textContent = data.completed_orders;
                    document.getElementById('shift-pending').textContent = data.pending_orders;
                }
            })
            .catch(error => console.error('Ошибка обновления статистики:', error));
    }

    // Обновляем статистику каждые 30 секунд
    setInterval(updateShiftStats, 30000);
});
</script>
@endif
@endsection
