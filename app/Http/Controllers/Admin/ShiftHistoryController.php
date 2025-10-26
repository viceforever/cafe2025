<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShiftHistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Shift::with(['user', 'orders']);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('start_time', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('start_time', '<=', $request->date_to);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $shifts = $query->orderBy('start_time', 'desc')->paginate(4);

        $employees = User::whereIn('role', ['manager', 'admin'])
            ->orderBy('last_name')
            ->get();

        Log::info('[v0] Admin viewing shift history', [
            'filters' => $request->only(['user_id', 'date_from', 'date_to', 'status']),
            'total_shifts' => $shifts->total()
        ]);

        return view('admin.shifts.index', compact('shifts', 'employees'));
    }

    public function show(Shift $shift)
    {
        $shift->load('user', 'orders.orderItems.product');

        $activeOrders = $shift->orders->filter(function ($order) {
            Log::info('[v0] Filtering order', [
                'order_id' => $order->id,
                'status' => $order->status,
                'is_cancelled' => $order->status === 'Отменен'
            ]);
            return $order->status !== 'Отменен';
        });

        Log::info('[v0] Active orders count', [
            'total_orders' => $shift->orders->count(),
            'active_orders' => $activeOrders->count()
        ]);

        $stats = [
            'total_orders' => $activeOrders->count(),
            'total_revenue' => $activeOrders->sum('total_amount'),
            'cash_sales' => $activeOrders->where('payment_method', 'cash')->sum('total_amount'),
            'card_sales' => $activeOrders->where('payment_method', 'card')->sum('total_amount'),
            'average_check' => $activeOrders->count() > 0 ? $activeOrders->sum('total_amount') / $activeOrders->count() : 0,
        ];

        return view('admin.shifts.show', compact('shift', 'stats'));
    }
}
