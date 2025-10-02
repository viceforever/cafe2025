<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index()
    {
        $users = User::paginate(15);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255|regex:/^[a-zA-Zа-яА-ЯёЁ]+$/u',
            'last_name' => 'required|string|max:255|regex:/^[a-zA-Zа-яА-ЯёЁ]+$/u',
            'phone' => 'required|string|min:11|max:11|unique:users,phone|regex:/^[0-9]+$/',
            'password' => 'required|string|min:8',
            'role' => ['required', Rule::in(['client', 'manager', 'admin'])],
        ], [
            'first_name.regex' => 'Поле "Имя" может содержать только буквы.',
            'last_name.regex' => 'Поле "Фамилия" может содержать только буквы.',
            'phone.regex' => 'Поле "Телефон" может содержать только цифры.',
            'phone.min' => 'Поле "Телефон" должно содержать ровно 11 цифр.',
            'phone.max' => 'Поле "Телефон" должно содержать ровно 11 цифр.',
            'phone.unique' => 'Номер телефона уже зарегистрирован.',
            'password.min' => 'Минимальная длина пароля 8 символов.',
        ]);

        User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Пользователь успешно создан');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'first_name' => 'required|string|max:255|regex:/^[a-zA-Zа-яА-ЯёЁ]+$/u',
            'last_name' => 'required|string|max:255|regex:/^[a-zA-Zа-яА-ЯёЁ]+$/u',
            'phone' => ['required', 'string', 'min:11', 'max:11', 'regex:/^[0-9]+$/', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'role' => ['required', Rule::in(['client', 'manager', 'admin'])],
        ], [
            'first_name.regex' => 'Поле "Имя" может содержать только буквы.',
            'last_name.regex' => 'Поле "Фамилия" может содержать только буквы.',
            'phone.regex' => 'Поле "Телефон" может содержать только цифры.',
            'phone.min' => 'Поле "Телефон" должно содержать ровно 11 цифр.',
            'phone.max' => 'Поле "Телефон" должно содержать ровно 11 цифр.',
            'phone.unique' => 'Номер телефона уже зарегистрирован.',
            'password.min' => 'Минимальная длина пароля 8 символов.',
        ]);

        $data = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'Данные пользователя обновлены');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Пользователь удален');
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => ['required', Rule::in(['client', 'manager', 'admin'])],
        ]);

        $user->update(['role' => $request->role]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Роль пользователя обновлена');
    }
}
