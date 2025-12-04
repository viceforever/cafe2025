<?php

namespace App\Http\Controllers;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        DB::beginTransaction();
        try {
            if ($order->status !== 'Отменен') {
                if (in_array($order->status, ['В обработке', 'Подтвержден'])) {
                    foreach ($order->orderItems as $orderItem) {
                        $product = $orderItem->product;
                        if ($product) {
                            for ($i = 0; $i < $orderItem->quantity; $i++) {
                                $product->restoreIngredients();
                            }
                        }
                    }
                }
            }
            
            $order->update(['status' => 'Отменен']);
            DB::commit();
            
            $message = in_array($order->status, ['В обработке', 'Подтвержден']) 
                ? 'Заказ успешно отменен, ингредиенты восстановлены'
                : 'Заказ успешно отменен';
            
            return redirect()->route('admin.orders.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('admin.orders.index')->with('error', 'Ошибка при отмене заказа: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:В обработке,Подтвержден,Готовится,Готов к выдаче,Выдан,Отменен'
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;

        DB::beginTransaction();
        try {
            if ($oldStatus === 'Отменен' && $newStatus !== 'Отменен') {
                foreach ($order->orderItems as $orderItem) {
                    $product = $orderItem->product;
                    if ($product) {
                        for ($i = 0; $i < $orderItem->quantity; $i++) {
                            $product->reduceIngredients();
                        }
                    }
                }
            }

            if ($newStatus === 'Готовится' && $oldStatus !== 'Готовится') {
                // Проверяем наличие всех ингредиентов перед переходом в готовку
                $missingIngredients = [];
                
                foreach ($order->orderItems as $orderItem) {
                    $product = $orderItem->product;
                    if ($product) {
                        foreach ($product->ingredients as $ingredient) {
                            $totalNeeded = $ingredient->pivot->quantity_needed * $orderItem->quantity;
                            
                            if (!$ingredient->canMakeProduct($totalNeeded)) {
                                $shortage = $totalNeeded - $ingredient->quantity;
                                $missingIngredients[] = sprintf(
                                    '%s (не хватает: %.2f %s)', 
                                    $ingredient->name, 
                                    $shortage, 
                                    $ingredient->unit
                                );
                            }
                        }
                    }
                }
                
                if (!empty($missingIngredients)) {
                    DB::rollback();
                    $errorMessage = 'Невозможно начать готовку. Недостаточно ингредиентов: ' . implode(', ', $missingIngredients);
                    return redirect()->route('admin.orders.index')->with('error', $errorMessage);
                }
            }
            
            if ($newStatus === 'Отменен' && in_array($oldStatus, ['В обработке', 'Подтвержден'])) {
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
            DB::commit();
            
            $message = match(true) {
                $oldStatus === 'Отменен' && $newStatus !== 'Отменен' => 'Статус изменен, ингредиенты зарезервированы',
                $newStatus === 'Готовится' && $oldStatus !== 'Готовится' => 'Статус изменен на "Готовится", проверка ингредиентов выполнена',
                $newStatus === 'Отменен' && in_array($oldStatus, ['В обработке', 'Подтвержден']) => 'Статус изменен на "Отменен", ингредиенты восстановлены',
                $newStatus === 'Отменен' => 'Статус изменен на "Отменен"',
                default => 'Статус заказа успешно изменен'
            };
            
            return redirect()->route('admin.orders.index')->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('admin.orders.index')->with('error', 'Ошибка при изменении статуса: ' . $e->getMessage());
        }
    }
}
