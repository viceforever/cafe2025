<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;


class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'last_name' => 'required|string|max:255|regex:/^[a-zA-Zа-яА-ЯёЁ]+$/u',
            'first_name' => 'required|string|max:255|regex:/^[a-zA-Zа-яА-ЯёЁ]+$/u',
            'phone' => 'required|string|max:11| min:11| unique:users,phone|regex:/^[0-9]+$/',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'phone.unique' => 'Номер телефона уже зарегистрирован',
            'password.min' => 'Минимальная длина пароля 8 символов',
            'password.confirmed' => 'Пароли не совпадают',
            'first_name.regex' => 'Поле "Имя" может содержать только буквы.',
            'last_name.regex' => 'Поле "Фамилия" может содержать только буквы.',
            'phone.regex' => 'Поле "Телефон" может содержать только цифры.',
            'first_name.max' => 'Поле "Имя" не должно превышать 255 символов.',
            'last_name.max' => 'Поле "Фамилия" не должно превышать 255 символов.',
            'phone.max' => 'Поле "Телефон" не должно превышать 11 символов.',
            'phone.min' => 'Поле "Телефон" не должно быть меньше 11 символов.',
        ]);
    
        User::create([
            'last_name' => $request->last_name,
            'first_name' => $request->first_name,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);
    
        return redirect()->route('login')->with('status', 'Регистрация успешна!');
    }
    
}

