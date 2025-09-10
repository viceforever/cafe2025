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
        $employees = User::whereIn('role', ['admin', 'manager'])->paginate(10);
        return view('admin.employees.index', compact('employees'));
    }

    public function create()
    {
        return view('admin.employees.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|min:6',
            'role' => ['required', Rule::in(['manager', 'admin'])],
        ]);

        User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('admin.employees.index')
            ->with('success', 'Сотрудник успешно создан');
    }

    public function edit(User $employee)
    {
        if ($employee->isClient()) {
            abort(404);
        }
        
        return view('admin.employees.edit', compact('employee'));
    }

    public function update(Request $request, User $employee)
    {
        if ($employee->isClient()) {
            abort(404);
        }

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => ['required', 'string', Rule::unique('users')->ignore($employee->id)],
            'password' => 'nullable|string|min:6',
            'role' => ['required', Rule::in(['manager', 'admin'])],
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

        $employee->update($data);

        return redirect()->route('admin.employees.index')
            ->with('success', 'Данные сотрудника обновлены');
    }

    public function destroy(User $employee)
    {
        if ($employee->isClient()) {
            abort(404);
        }

        $employee->delete();

        return redirect()->route('admin.employees.index')
            ->with('success', 'Сотрудник удален');
    }
}
