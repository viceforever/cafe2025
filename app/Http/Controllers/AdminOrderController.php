<?php

namespace App\Http\Controllers;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'orderItems.product']);
        
        // Фильтрация по статусу
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        $orders = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Получаем все возможные статусы для фильтра
        $statuses = ['В обработке', 'Подтвержден', 'Готовится', 'Готов к выдаче', 'Выдан', 'Отменен'];
        
        return view('admin.orders.index', compact('orders', 'statuses'));
    }
    
    public function confirm(Order $order)
    {
        $order->update(['status' => 'Подтвержден']);
        return redirect()->route('admin.orders.index')->with('success', 'Заказ успешно подтвержден');
    }

    public function cancel(Order $order)
    {
        if ($order->status !== 'Отменен') {
            foreach ($order->orderItems as $orderItem) {
                $product = $orderItem->product;
                if ($product) {
                    for ($i = 0; $i < $orderItem->quantity; $i++) {
                        $product->restoreIngredients();
                    }
                }
            }
        }
        
        $order->update(['status' => 'Отменен']);
        return redirect()->route('admin.orders.index')->with('success', 'Заказ успешно отменен, ингредиенты восстановлены');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:В обработке,Подтвержден,Готовится,Готов к выдаче,Выдан,Отменен'
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;

        if ($oldStatus !== 'Отменен' && $newStatus === 'Отменен') {
            foreach ($order->orderItems as $orderItem) {
                $product = $orderItem->product;
                if ($product) {
                    for ($i = 0; $i < $orderItem->quantity; $i++) {
                        $product->restoreIngredients();
                    }
                }
            }
        }

        $order->update(['status' => $newStatus]);
        
        $message = $newStatus === 'Отменен' ? 
            'Статус заказа изменен на "Отменен", ингредиенты восстановлены' : 
            'Статус заказа успешно изменен';
        
        return redirect()->route('admin.orders.index')->with('success', $message);
    }
}
