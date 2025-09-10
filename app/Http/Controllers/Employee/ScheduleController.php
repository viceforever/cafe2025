<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
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

        $schedules = Schedule::forUser(Auth::id())
                           ->forWeek($startOfWeek)
                           ->with('creator')
                           ->get()
                           ->keyBy(function ($schedule) {
                               return $schedule->date->format('Y-m-d');
                           });

        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $weekDays[] = $startOfWeek->copy()->addDays($i);
        }

        $nextWeek = $startOfWeek->copy()->addWeek()->format('Y-m-d');
        $prevWeek = $startOfWeek->copy()->subWeek()->format('Y-m-d');

        return view('employee.schedule.index', compact('schedules', 'weekDays', 'currentWeek', 'nextWeek', 'prevWeek'));
    }

    public function upcoming()
    {
        $upcomingSchedules = Schedule::forUser(Auth::id())
                                   ->where('date', '>=', now()->format('Y-m-d'))
                                   ->orderBy('date')
                                   ->take(10)
                                   ->get();

        return view('employee.schedule.upcoming', compact('upcomingSchedules'));
    }
}
