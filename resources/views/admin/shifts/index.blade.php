@extends('layouts.app')

@section('content')
<div class="container" style="padding-top: 220px;">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4">История смен сотрудников</h2>

            {{-- Фильтры --}}
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.shifts.index') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="user_id" class="form-label">Сотрудник</label>
                                <select name="user_id" id="user_id" class="form-select">
                                    <option value="">Все сотрудники</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" 
                                            {{ request('user_id') == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->last_name }} {{ $employee->first_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label for="date_from" class="form-label">Дата с</label>
                                <input type="date" name="date_from" id="date_from" 
                                    class="form-control" value="{{ request('date_from') }}">
                            </div>

                            <div class="col-md-2">
                                <label for="date_to" class="form-label">Дата по</label>
                                <input type="date" name="date_to" id="date_to" 
                                    class="form-control" value="{{ request('date_to') }}">
                            </div>

                            <div class="col-md-2">
                                <label for="status" class="form-label">Статус</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="">Все статусы</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>
                                        Активна
                                    </option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>
                                        Завершена
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-3 d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-primary">
                                    Применить
                                </button>
                                <a href="{{ route('admin.shifts.index') }}" class="btn btn-secondary">
                                    Сбросить
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Таблица смен --}}
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>№</th>
                                    <th>Сотрудник</th>
                                    <th>Дата</th>
                                    <th>Время начала</th>
                                    <th>Время окончания</th>
                                    <th>Длительность</th>
                                    <th>Заказов</th>
                                    <th>Выручка</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($shifts as $shift)
                                    <tr>
                                        <td>{{ $shift->id }}</td>
                                        <td>
                                            <strong>{{ $shift->user->last_name }} {{ $shift->user->first_name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $shift->user->phone }}</small>
                                        </td>
                                        <td>{{ date('d.m.Y', strtotime($shift->start_time)) }}</td>
                                        <td>{{ date('H:i', strtotime($shift->start_time)) }}</td>
                                        <td>
                                            @if($shift->end_time)
                                                {{ date('H:i', strtotime($shift->end_time)) }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($shift->start_time && $shift->end_time)
                                                {{ $shift->duration }}
                                            @else
                                                <span class="badge bg-success">В процессе</span>
                                            @endif
                                        </td>
                                        {{-- Вычисляем значения из активных заказов --}}
                                        <td>{{ $shift->active_orders->count() }}</td>
                                        <td>{{ number_format($shift->active_orders->sum('total_amount'), 2) }} ₽</td>
                                        <td>
                                            <span class="badge bg-{{ $shift->isActive() ? 'success' : 'secondary' }}">
                                                {{ $shift->isActive() ? 'Активна' : 'Завершена' }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.shifts.show', $shift) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                Подробнее
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">Смены не найдены</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{ $shifts->links('custom.pagination') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
