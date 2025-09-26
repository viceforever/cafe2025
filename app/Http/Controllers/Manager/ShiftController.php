<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::where('user_id', Auth::id())
                      ->orderBy('start_time', 'desc')
                      ->paginate(15);

        foreach ($shifts as $shift) {
            Log::info('[SHIFT CONTROLLER DEBUG] Смена в списке', [
                'shift_id' => $shift->id,
                'start_time_raw' => $shift->getOriginal('start_time'),
                'start_time_value' => $shift->start_time,
                'timezone' => config('app.timezone')
            ]);
        }

        return view('manager.shifts.index', compact('shifts'));
    }

    public function show(Shift $shift)
    {
        if ($shift->user_id !== Auth::id()) {
            abort(403);
        }

        Log::info('[SHIFT CONTROLLER DEBUG] Подробности смены', [
            'shift_id' => $shift->id,
            'start_time_raw' => $shift->getOriginal('start_time'),
            'start_time_value' => $shift->start_time,
            'end_time_raw' => $shift->getOriginal('end_time'),
            'end_time_value' => $shift->end_time,
            'timezone' => config('app.timezone')
        ]);

        $orders = $shift->orders()->with('orderItems.product')->get();

        return view('manager.shifts.show', compact('shift', 'orders'));
    }
}
