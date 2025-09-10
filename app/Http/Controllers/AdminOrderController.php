<?php

namespace App\Http\Controllers;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['user', 'orderItems.product'])
            ->where('status', 'В обработке')
            ->get();
        return view('admin.orders.index', compact('orders'));
    }
    public function confirm(Order $order)
    {
        $order->update(['status' => 'Подтвержден']);
        return redirect()->route('admin.orders.index')->with('success', 'Заказ успешно подтвержден');
    }
}
