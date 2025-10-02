@extends('layouts.app')

@section('content')
<div class="container" style="padding-top: 220px;">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h4>Редактировать пользователя</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.users.update', $user) }}" method="POST" id="editUserForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="first_name" class="form-label">Имя</label>
                            <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                   id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}" required>
                            @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            {{-- Добавлено сообщение об ошибке валидации --}}
                            <div id="first-name-error" class="mt-1" style="display: none; color: #000;"></div>
                        </div>

                        <div class="mb-3">
                            <label for="last_name" class="form-label">Фамилия</label>
                            <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                   id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
                            @error('last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            {{-- Добавлено сообщение об ошибке валидации --}}
                            <div id="last-name-error" class="mt-1" style="display: none; color: #000;"></div>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Телефон</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" value="{{ old('phone', $user->phone) }}" required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            {{-- Добавлено сообщение об ошибке валидации --}}
                            <div id="phone-error" class="mt-1" style="display: none; color: #000;"></div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Пароль (оставьте пустым, если не хотите менять)</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            {{-- Добавлено сообщение об ошибке валидации --}}
                            <div id="password-error" class="mt-1" style="display: none; color: #000;"></div>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Роль</label>
                            <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                <option value="">Выберите роль</option>
                                <option value="client" {{ old('role', $user->role) === 'client' ? 'selected' : '' }}>Клиент</option>
                                <option value="manager" {{ old('role', $user->role) === 'manager' ? 'selected' : '' }}>Менеджер</option>
                                <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Администратор</option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Отмена</a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">Обновить пользователя</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Добавлена JavaScript валидация полей --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editUserForm');
    const firstNameInput = document.getElementById('first_name');
    const lastNameInput = document.getElementById('last_name');
    const phoneInput = document.getElementById('phone');
    const passwordInput = document.getElementById('password');
    const submitBtn = document.getElementById('submitBtn');
    
    const firstNameError = document.getElementById('first-name-error');
    const lastNameError = document.getElementById('last-name-error');
    const phoneError = document.getElementById('phone-error');
    const passwordError = document.getElementById('password-error');

    // Валидация имени (только буквы)
    firstNameInput.addEventListener('input', function() {
        const value = this.value;
        const lettersOnly = /^[a-zA-Zа-яА-ЯёЁ]*$/u;
        
        if (value && !lettersOnly.test(value)) {
            firstNameError.textContent = 'Имя может содержать только буквы';
            firstNameError.style.display = 'block';
            submitBtn.disabled = true;
        } else {
            firstNameError.style.display = 'none';
            checkFormValidity();
        }
    });

    // Валидация фамилии (только буквы)
    lastNameInput.addEventListener('input', function() {
        const value = this.value;
        const lettersOnly = /^[a-zA-Zа-яА-ЯёЁ]*$/u;
        
        if (value && !lettersOnly.test(value)) {
            lastNameError.textContent = 'Фамилия может содержать только буквы';
            lastNameError.style.display = 'block';
            submitBtn.disabled = true;
        } else {
            lastNameError.style.display = 'none';
            checkFormValidity();
        }
    });

    // Валидация телефона (только цифры, ровно 11 символов)
    phoneInput.addEventListener('input', function() {
        const value = this.value;
        const numbersOnly = /^[0-9]*$/;
        
        if (value && !numbersOnly.test(value)) {
            phoneError.textContent = 'Телефон может содержать только цифры';
            phoneError.style.display = 'block';
            submitBtn.disabled = true;
        } else if (value && value.length !== 11) {
            phoneError.textContent = 'Телефон должен содержать ровно 11 цифр';
            phoneError.style.display = 'block';
            submitBtn.disabled = true;
        } else {
            phoneError.style.display = 'none';
            checkFormValidity();
        }
    });

    // Валидация пароля (минимум 8 символов, если заполнено)
    passwordInput.addEventListener('input', function() {
        const value = this.value;
        
        if (value && value.length < 8) {
            passwordError.textContent = 'Пароль должен содержать минимум 8 символов';
            passwordError.style.display = 'block';
            submitBtn.disabled = true;
        } else {
            passwordError.style.display = 'none';
            checkFormValidity();
        }
    });

    // Проверка валидности всей формы
    function checkFormValidity() {
        const firstNameValid = firstNameInput.value && /^[a-zA-Zа-яА-ЯёЁ]+$/u.test(firstNameInput.value);
        const lastNameValid = lastNameInput.value && /^[a-zA-Zа-яА-ЯёЁ]+$/u.test(lastNameInput.value);
        const phoneValid = phoneInput.value && /^[0-9]{11}$/.test(phoneInput.value);
        const passwordValid = !passwordInput.value || passwordInput.value.length >= 8;
        
        submitBtn.disabled = !(firstNameValid && lastNameValid && phoneValid && passwordValid);
    }

    // Валидация при отправке формы
    form.addEventListener('submit', function(e) {
        const firstNameValid = /^[a-zA-Zа-яА-ЯёЁ]+$/u.test(firstNameInput.value);
        const lastNameValid = /^[a-zA-Zа-яА-ЯёЁ]+$/u.test(lastNameInput.value);
        const phoneValid = /^[0-9]{11}$/.test(phoneInput.value);
        const passwordValid = !passwordInput.value || passwordInput.value.length >= 8;
        
        if (!firstNameValid || !lastNameValid || !phoneValid || !passwordValid) {
            e.preventDefault();
            alert('Пожалуйста, исправьте ошибки в форме перед отправкой');
        }
    });
});
</script>
@endsection
