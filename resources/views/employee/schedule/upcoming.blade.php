@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Ближайшие смены</h2>
                <a href="{{ route('employee.schedule.index') }}" class="btn btn-secondary">
                    <i class="iconify" data-icon="mdi:calendar"></i> К календарю
                </a>
            </div>

            @forelse($upcomingSchedules as $schedule)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <h6 class="mb-0 {{ $schedule->isToday() ? 'text-primary' : '' }}">
                                    {{ $schedule->date->format('d.m.Y') }}
                                    @if($schedule->isToday())
                                        <span class="badge bg-primary ms-1">Сегодня</span>
                                    @endif
                                </h6>
                                <small class="text-muted">{{ $schedule->date->format('l') }}</small>
                            </div>
                            <div class="col-md-3">
                                <strong>{{ $schedule->formatted_time }}</strong>
                                <br>
                                <small class="text-muted">{{ $schedule->duration }} часов</small>
                            </div>
                            <div class="col-md-4">
                                @if($schedule->notes)
                                    <small class="text-muted">{{ $schedule->notes }}</small>
                                @else
                                    <small class="text-muted">Без комментариев</small>
                                @endif
                            </div>
                            <div class="col-md-2 text-end">
                                @if($schedule->isToday())
                                    <span class="badge bg-success">Рабочий день</span>
                                @else
                                    <span class="badge bg-primary">Запланировано</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="iconify text-muted mb-3" data-icon="mdi:calendar-remove" style="font-size: 4rem;"></i>
                        <h5 class="text-muted">Нет запланированных смен</h5>
                        <p class="text-muted">Обратитесь к администратору для составления графика</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
