<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $orders = $user->orders()->orderBy('created_at', 'desc')->get();
        return view('profile.show', compact('user', 'orders'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'first_name' => 'required|string|max:255|regex:/^[a-zA-Zа-яА-ЯёЁ]+$/u',
            'last_name' => 'required|string|max:255|regex:/^[a-zA-Zа-яА-ЯёЁ]+$/u',
            'phone' => 'required|string|max:11|min:11|regex:/^[0-9]+$/',
        ], [
            'first_name.regex' => 'Поле "Имя" может содержать только буквы.',
            'last_name.regex' => 'Поле "Фамилия" может содержать только буквы.',
            'phone.regex' => 'Поле "Телефон" может содержать только цифры.',
            'first_name.max' => 'Поле "Имя" не должно превышать 255 символов.',
            'last_name.max' => 'Поле "Фамилия" не должно превышать 255 символов.',
            'phone.max' => 'Поле "Телефон" не должно превышать 11 символов.',
            'phone.min' => 'Поле "Телефон" не должно быть меньше 11 символов.',
        ]);
        

        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->phone = $request->phone;
        $user->save();

        return redirect()->route('profile.show')->with('success', 'Профиль успешно обновлен');
    }
}