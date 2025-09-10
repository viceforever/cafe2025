@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Управление графиками работы</h2>
                <div>
                    <a href="{{ route('admin.schedules.bulk-create', ['start_date' => $currentWeek]) }}" 
                       class="btn btn-info me-2">
                        <i class="iconify" data-icon="mdi:calendar-multiple"></i> Массовое создание
                    </a>
                    <a href="{{ route('admin.schedules.create') }}" class="btn btn-primary">
                        <i class="iconify" data-icon="mdi:plus"></i> Добавить график
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning">{{ session('warning') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <!-- Навигация по неделям -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('admin.schedules.index', ['week' => \Carbon\Carbon::parse($currentWeek)->subWeek()->format('Y-m-d')]) }}" 
                           class="btn btn-outline-primary">
                            <i class="iconify" data-icon="mdi:chevron-left"></i> Предыдущая неделя
                        </a>
                        <h5 class="mb-0">
                            Неделя: {{ \Carbon\Carbon::parse($currentWeek)->format('d.m.Y') }} - 
                            {{ \Carbon\Carbon::parse($currentWeek)->endOfWeek()->format('d.m.Y') }}
                        </h5>
                        <a href="{{ route('admin.schedules.index', ['week' => \Carbon\Carbon::parse($currentWeek)->addWeek()->format('Y-m-d')]) }}" 
                           class="btn btn-outline-primary">
                            Следующая неделя <i class="iconify" data-icon="mdi:chevron-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- График работы -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Сотрудник</th>
                                    @foreach($weekDays as $day)
                                        <th class="text-center {{ $day->isToday() ? 'bg-primary text-white' : '' }}">
                                            {{ $day->format('D') }}<br>
                                            <small>{{ $day->format('d.m') }}</small>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employees as $employee)
                                    <tr>
                                        <td>
                                            <strong>{{ $employee->first_name }} {{ $employee->last_name }}</strong><br>
                                            <small class="text-muted">{{ $employee->role === 'admin' ? 'Администратор' : 'Менеджер' }}</small>
                                        </td>
                                        @foreach($weekDays as $day)
                                            @php
                                                $daySchedule = $schedules->get($employee->id)?->firstWhere('date', $day->format('Y-m-d'));
                                            @endphp
                                            <td class="text-center {{ $day->isToday() ? 'bg-light' : '' }}">
                                                @if($daySchedule)
                                                    <div class="badge bg-success mb-1">
                                                        {{ $daySchedule->formatted_time }}
                                                    </div>
                                                    <br>
                                                    <small>{{ $daySchedule->duration }}ч</small>
                                                    @if($daySchedule->notes)
                                                        <br><small class="text-muted">{{ Str::limit($daySchedule->notes, 20) }}</small>
                                                    @endif
                                                    <div class="mt-1">
                                                        <a href="{{ route('admin.schedules.edit', $daySchedule) }}" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="iconify" data-icon="mdi:pencil"></i>
                                                        </a>
                                                        <form action="{{ route('admin.schedules.destroy', $daySchedule) }}" 
                                                              method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                                    onclick="return confirm('Удалить график?')">
                                                                <i class="iconify" data-icon="mdi:delete"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                @else
                                                    <a href="{{ route('admin.schedules.create', ['date' => $day->format('Y-m-d'), 'user_id' => $employee->id]) }}" 
                                                       class="btn btn-sm btn-outline-secondary">
                                                        <i class="iconify" data-icon="mdi:plus"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Сотрудники не найдены</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
