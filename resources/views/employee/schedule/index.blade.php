@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Мой график работы</h2>
                <a href="{{ route('employee.schedule.upcoming') }}" class="btn btn-outline-primary">
                    <i class="iconify" data-icon="mdi:calendar-clock"></i> Ближайшие смены
                </a>
            </div>

            <!-- Навигация по неделям -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('employee.schedule.index', ['week' => $prevWeek]) }}" 
                           class="btn btn-outline-primary">
                            <i class="iconify" data-icon="mdi:chevron-left"></i> Предыдущая неделя
                        </a>
                        <h5 class="mb-0">
                            Неделя: {{ \Carbon\Carbon::parse($currentWeek)->format('d.m.Y') }} - 
                            {{ \Carbon\Carbon::parse($currentWeek)->endOfWeek()->format('d.m.Y') }}
                        </h5>
                        <a href="{{ route('employee.schedule.index', ['week' => $nextWeek]) }}" 
                           class="btn btn-outline-primary">
                            Следующая неделя <i class="iconify" data-icon="mdi:chevron-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- График работы -->
            <div class="row">
                @foreach($weekDays as $day)
                    @php
                        $daySchedule = $schedules->get($day->format('Y-m-d'));
                        $isToday = $day->isToday();
                        $isPast = $day->isPast();
                    @endphp
                    <div class="col-md-12 mb-3">
                        <div class="card {{ $isToday ? 'border-primary' : '' }}">
                            <div class="card-header {{ $isToday ? 'bg-primary text-white' : '' }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">
                                        {{ $day->format('l, d.m.Y') }}
                                        @if($isToday)
                                            <span class="badge bg-light text-primary ms-2">Сегодня</span>
                                        @endif
                                    </h6>
                                    @if($daySchedule)
                                        <span class="badge {{ $isToday ? 'bg-light text-primary' : 'bg-success' }}">
                                            {{ $daySchedule->formatted_time }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body">
                                @if($daySchedule)
                                    <div class="row">
                                        <div class="col-md-8">
                                            <p class="mb-1">
                                                <strong>Время работы:</strong> {{ $daySchedule->formatted_time }}
                                            </p>
                                            <p class="mb-1">
                                                <strong>Продолжительность:</strong> {{ $daySchedule->duration }}
                                            </p>
                                            @if($daySchedule->notes)
                                                <p class="mb-1">
                                                    <strong>Комментарии:</strong> {{ $daySchedule->notes }}
                                                </p>
                                            @endif
                                            <small class="text-muted">
                                                График создан: {{ $daySchedule->creator->first_name }} {{ $daySchedule->creator->last_name }}
                                            </small>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            @if($isToday)
                                                <div class="alert alert-info mb-0">
                                                    <i class="iconify" data-icon="mdi:clock"></i>
                                                    Рабочий день
                                                </div>
                                            @elseif($isPast)
                                                <div class="text-muted">
                                                    <i class="iconify" data-icon="mdi:check"></i>
                                                    Завершено
                                                </div>
                                            @else
                                                <div class="text-primary">
                                                    <i class="iconify" data-icon="mdi:calendar-clock"></i>
                                                    Запланировано
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center text-muted py-3">
                                        <i class="iconify" data-icon="mdi:calendar-remove" style="font-size: 2rem;"></i>
                                        <p class="mb-0 mt-2">Выходной день</p>
                                    </div>
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
