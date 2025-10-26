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
                      ->withCount(['orders' => function ($query) {
                          $query->where('status', '<>', 'Отменен');
                      }])
                      ->withSum(['orders' => function ($query) {
                          $query->where('status', '<>', 'Отменен');
                      }], 'total_amount')
                      ->orderBy('start_time', 'desc')
                      ->paginate(3);

        foreach ($shifts as $shift) {
            $shift->total_orders = $shift->orders_count ?? 0;
            $shift->total_revenue = $shift->orders_sum_total_amount ?? 0;
            
            Log::info('[SHIFT CONTROLLER DEBUG] Смена в списке', [
                'shift_id' => $shift->id,
                'start_time_raw' => $shift->getOriginal('start_time'),
                'start_time_value' => $shift->start_time,
                'timezone' => config('app.timezone'),
                'total_orders' => $shift->total_orders,
                'total_revenue' => $shift->total_revenue
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
        
        $activeOrders = $orders->filter(function ($order) {
            Log::info('[v0] Проверка заказа', [
                'order_id' => $order->id,
                'status' => $order->status,
                'is_cancelled' => $order->status === 'Отменен'
            ]);
            return $order->status !== 'Отменен';
        });

        Log::info('[v0] Статистика заказов', [
            'total_orders' => $orders->count(),
            'active_orders' => $activeOrders->count(),
            'cancelled_orders' => $orders->count() - $activeOrders->count()
        ]);

        $stats = [
            'total_orders' => $activeOrders->count(),
            'total_revenue' => $activeOrders->sum('total_amount'),
            'cash_sales' => $activeOrders->where('payment_method', 'cash')->sum('total_amount'),
            'card_sales' => $activeOrders->where('payment_method', 'card')->sum('total_amount'),
        ];

        return view('manager.shifts.show', compact('shift', 'orders', 'stats'));
    }
}
