<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $currentWeek = $request->get('week', now()->startOfWeek()->format('Y-m-d'));
        $startOfWeek = Carbon::parse($currentWeek)->startOfWeek();
        $endOfWeek = $startOfWeek->copy()->endOfWeek();

        $employees = User::whereIn('role', ['manager', 'admin'])->get();
        
        $schedules = Schedule::with(['user', 'creator'])
                           ->forWeek($startOfWeek)
                           ->get()
                           ->groupBy('user_id');

        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $weekDays[] = $startOfWeek->copy()->addDays($i);
        }

        return view('admin.schedules.index', compact('schedules', 'employees', 'weekDays', 'currentWeek'));
    }

    public function create(Request $request)
    {
        $employees = User::whereIn('role', ['manager', 'admin'])->get();
        $selectedDate = $request->get('date', now()->format('Y-m-d'));
        
        return view('admin.schedules.create', compact('employees', 'selectedDate'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'notes' => 'nullable|string|max:500',
        ], [
            'user_id.required' => 'Выберите сотрудника',
            'date.required' => 'Укажите дату',
            'date.after_or_equal' => 'Дата не может быть в прошлом',
            'start_time.required' => 'Укажите время начала',
            'end_time.required' => 'Укажите время окончания',
            'end_time.after' => 'Время окончания должно быть позже времени начала',
        ]);

        // Проверяем, нет ли уже графика на эту дату для этого сотрудника
        $existingSchedule = Schedule::where('user_id', $request->user_id)
                                  ->where('date', $request->date)
                                  ->first();

        if ($existingSchedule) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'На эту дату уже есть график для данного сотрудника');
        }

        Schedule::create([
            'user_id' => $request->user_id,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'notes' => $request->notes,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.schedules.index')
                        ->with('success', 'График работы создан');
    }

    public function edit(Schedule $schedule)
    {
        $employees = User::whereIn('role', ['manager', 'admin'])->get();
        
        return view('admin.schedules.edit', compact('schedule', 'employees'));
    }

    public function update(Request $request, Schedule $schedule)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'notes' => 'nullable|string|max:500',
        ]);

        // Проверяем, нет ли конфликта с другими графиками
        $existingSchedule = Schedule::where('user_id', $request->user_id)
                                  ->where('date', $request->date)
                                  ->where('id', '!=', $schedule->id)
                                  ->first();

        if ($existingSchedule) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'На эту дату уже есть график для данного сотрудника');
        }

        $schedule->update($request->all());

        return redirect()->route('admin.schedules.index')
                        ->with('success', 'График работы обновлен');
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        return redirect()->route('admin.schedules.index')
                        ->with('success', 'График работы удален');
    }

    public function list(Request $request)
    {
        $query = Schedule::with(['user', 'creator'])
                        ->orderBy('date', 'desc')
                        ->orderBy('start_time', 'asc');

        // Фильтрация по сотруднику
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Фильтрация по дате
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        $schedules = $query->paginate(15);
        $employees = User::whereIn('role', ['manager', 'admin'])->get();

        return view('admin.schedules.list', compact('schedules', 'employees'));
    }

    public function bulkCreate(Request $request)
    {
        $employees = User::whereIn('role', ['manager', 'admin'])->get();
        $startDate = $request->get('start_date', now()->startOfWeek()->format('Y-m-d'));
        
        return view('admin.schedules.bulk-create', compact('employees', 'startDate'));
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'schedules' => 'required|array',
            'schedules.*.user_id' => 'required|exists:users,id',
            'schedules.*.date' => 'required|date',
            'schedules.*.start_time' => 'required|date_format:H:i',
            'schedules.*.end_time' => 'required|date_format:H:i|after:schedules.*.start_time',
        ]);

        $created = 0;
        $errors = [];

        foreach ($request->schedules as $scheduleData) {
            if (empty($scheduleData['user_id']) || empty($scheduleData['date'])) {
                continue;
            }

            $existingSchedule = Schedule::where('user_id', $scheduleData['user_id'])
                                      ->where('date', $scheduleData['date'])
                                      ->first();

            if ($existingSchedule) {
                $user = User::find($scheduleData['user_id']);
                $errors[] = "График для {$user->first_name} {$user->last_name} на {$scheduleData['date']} уже существует";
                continue;
            }

            Schedule::create([
                'user_id' => $scheduleData['user_id'],
                'date' => $scheduleData['date'],
                'start_time' => $scheduleData['start_time'],
                'end_time' => $scheduleData['end_time'],
                'notes' => $scheduleData['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $created++;
        }

        $message = "Создано графиков: {$created}";
        if (!empty($errors)) {
            $message .= ". Ошибки: " . implode(', ', $errors);
        }

        return redirect()->route('admin.schedules.index')
                        ->with($created > 0 ? 'success' : 'warning', $message);
    }
}
