@extends('layouts.app')

@section('content')
<div class="container" style="padding-top: 220px;">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Все графики работы</h2>
                <div>
                    <a href="{{ route('admin.schedules.index') }}" class="btn btn-secondary me-2">
                        <i class="iconify" data-icon="mdi:arrow-left"></i> Назад к календарю
                    </a>
                    <a href="{{ route('admin.schedules.create') }}" class="btn btn-primary">
                        <i class="iconify" data-icon="mdi:plus"></i> Добавить график
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <!-- Фильтры -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.schedules.list') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="user_id" class="form-label">Сотрудник</label>
                                <select name="user_id" id="user_id" class="form-select">
                                    <option value="">Все сотрудники</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ request('user_id') == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->first_name }} {{ $employee->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="date_from" class="form-label">Дата с</label>
                                <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="date_to" class="form-label">Дата по</label>
                                <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="iconify" data-icon="mdi:filter"></i> Фильтр
                                </button>
                                <a href="{{ route('admin.schedules.list') }}" class="btn btn-outline-secondary">
                                    <i class="iconify" data-icon="mdi:filter-remove"></i> Сбросить
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Список графиков -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Сотрудник</th>
                                    <th>Дата</th>
                                    <th>Время</th>
                                    <th>Продолжительность</th>
                                    <th>Комментарии</th>
                                    <th>Создал</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($schedules as $schedule)
                                    <tr>
                                        <td>
                                            <strong>{{ $schedule->user->first_name }} {{ $schedule->user->last_name }}</strong><br>
                                            <small class="text-muted">{{ $schedule->user->role === 'admin' ? 'Администратор' : 'Менеджер' }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary text-white">
                                                {{ \Carbon\Carbon::parse($schedule->date)->format('d.m.Y') }}
                                            </span><br>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($schedule->date)->locale('ru')->dayName }}</small>
                                        </td>
                                        <td>
                                            <strong>{{ $schedule->formatted_time }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary text-white">{{ $schedule->duration }}</span>
                                        </td>
                                        <td>
                                            @if($schedule->notes)
                                                <span class="text-muted">{{ Str::limit($schedule->notes, 50) }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $schedule->creator->first_name ?? 'Неизвестно' }}<br>
                                                {{ $schedule->created_at->format('d.m.Y H:i') }}
                                            </small>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('admin.schedules.edit', $schedule) }}" 
                                                   class="btn btn-sm btn-primary">
                                                    Редактировать
                                                </a>
                                                <form action="{{ route('admin.schedules.destroy', $schedule) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                            onclick="return confirm('Вы уверены?')">
                                                        Удалить
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Графики не найдены</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Пагинация -->
                    @if($schedules->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $schedules->appends(request()->query())->links('custom.pagination') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
