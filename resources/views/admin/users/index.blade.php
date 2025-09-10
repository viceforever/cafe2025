@extends('layouts.app')

@section('content')
<div class="container" style="padding-top: 100px;">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Управление пользователями</h2>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                    <i class="iconify" data-icon="mdi:plus"></i> Добавить пользователя
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
                                    <th>Дата регистрации</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr>
                                        <td>{{ $user->id }}</td>
                                        <td>{{ $user->first_name }}</td>
                                        <td>{{ $user->last_name }}</td>
                                        <td>{{ $user->phone }}</td>
                                        <td>
                                            <form action="{{ route('admin.users.update-role', $user) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                                                    <option value="client" {{ $user->role === 'client' ? 'selected' : '' }}>Клиент</option>
                                                    <option value="manager" {{ $user->role === 'manager' ? 'selected' : '' }}>Менеджер</option>
                                                    <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Администратор</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td>{{ $user->created_at->format('d.m.Y') }}</td>
                                        <td>
                                            <a href="{{ route('admin.users.edit', $user) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="iconify" data-icon="mdi:pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.users.destroy', $user) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Удалить пользователя?')">
                                                    <i class="iconify" data-icon="mdi:delete"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Пользователи не найдены</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
