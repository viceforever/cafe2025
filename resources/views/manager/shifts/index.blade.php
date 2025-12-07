@extends('layouts.app')

@section('content')
<div class="container" style="padding-top: 220px;">
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
                                        <td>{{ date('d.m.Y', strtotime($shift->start_time)) }}</td>
                                        <td>{{ date('H:i', strtotime($shift->start_time)) }}</td>
                                        <td>
                                            @if($shift->end_time)
                                                {{ date('H:i', strtotime($shift->end_time)) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($shift->start_time && $shift->end_time)
                                                {{ $shift->duration }}
                                            @else
                                                В процессе
                                            @endif
                                        </td>
                                        {{-- Используем динамически подсчитанные значения вместо полей из БД --}}
                                        <td>{{ $shift->total_orders ?? 0 }}</td>
                                        <td>{{ number_format($shift->total_revenue ?? 0, 2) }} ₽</td>
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
                    
                    {{-- заменил стандартную пагинацию на кастомную русскую --}}
                    {{ $shifts->links('custom.pagination') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection