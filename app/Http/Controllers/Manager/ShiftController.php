<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::where('user_id', Auth::id())
                      ->orderBy('start_time', 'desc')
                      ->paginate(15);

        return view('manager.shifts.index', compact('shifts'));
    }

    public function show(Shift $shift)
    {
        if ($shift->user_id !== Auth::id()) {
            abort(403);
        }

        $orders = $shift->orders()->with('orderItems.product')->get();

        return view('manager.shifts.show', compact('shift', 'orders'));
    }
}
