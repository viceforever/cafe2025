@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4">История смен</h2>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
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
                                        <td>{{ $shift->start_time->format('d.m.Y') }}</td>
                                        <td>{{ $shift->start_time->format('H:i') }}</td>
                                        <td>{{ $shift->end_time ? $shift->end_time->format('H:i') : '-' }}</td>
                                        <td>{{ $shift->duration ?? 'В процессе' }}</td>
                                        <td>{{ $shift->total_orders }}</td>
                                        <td>{{ number_format($shift->total_revenue, 2) }} ₽</td>
                                        <td>
                                            <span class="badge bg-{{ $shift->isActive() ? 'success' : 'secondary' }}">
                                                {{ $shift->isActive() ? 'Активна' : 'Завершена' }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('manager.shifts.show', $shift) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                Подробнее
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Смены не найдены</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{ $shifts->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
