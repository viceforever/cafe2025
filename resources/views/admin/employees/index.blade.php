@extends('layouts.app')

@section('content')
<!-- Added padding-top to prevent navigation overlap -->
<div class="container" style="padding-top: 100px;">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Управление сотрудниками</h2>
                <a href="{{ route('admin.employees.create') }}" class="btn btn-primary">
                    <i class="iconify" data-icon="mdi:plus"></i> Добавить сотрудника
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Имя</th>
                                    <th>Фамилия</th>
                                    <th>Телефон</th>
                                    <th>Роль</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employees as $employee)
                                    <tr>
                                        <td>{{ $employee->id }}</td>
                                        <td>{{ $employee->first_name }}</td>
                                        <td>{{ $employee->last_name }}</td>
                                        <td>{{ $employee->phone }}</td>
                                        <td>
                                            <span class="badge bg-{{ $employee->role === 'admin' ? 'danger' : 'warning' }}">
                                                {{ $employee->role === 'admin' ? 'Администратор' : 'Менеджер' }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.employees.edit', $employee) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="iconify" data-icon="mdi:pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.employees.destroy', $employee) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Удалить сотрудника?')">
                                                    <i class="iconify" data-icon="mdi:delete"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Сотрудники не найдены</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- заменил стандартную пагинацию на кастомную русскую --}}
                    {{ $employees->links('custom.pagination') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
