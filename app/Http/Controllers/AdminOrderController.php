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
            DB::commit();
            
            return redirect()->route('admin.orders.index')->with('success', 'Заказ успешно отменен, ингредиенты восстановлены');
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
            // Если меняем статус С "Отменен" НА любой другой - нужно списать ингредиенты обратно
            if ($oldStatus === 'Отменен' && $newStatus !== 'Отменен') {
                // Проверяем наличие всех ингредиентов перед списанием
                $missingIngredients = [];
                
                foreach ($order->orderItems as $orderItem) {
                    $product = $orderItem->product;
                    if ($product) {
                        // Проверяем каждый ингредиент для каждого товара
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
                
                // Если есть недостающие ингредиенты - отменяем операцию
                if (!empty($missingIngredients)) {
                    DB::rollback();
                    $errorMessage = 'Невозможно изменить статус заказа. Недостаточно ингредиентов: ' . implode(', ', $missingIngredients);
                    return redirect()->route('admin.orders.index')->with('error', $errorMessage);
                }
                
                // Если все ингредиенты есть - списываем их
                foreach ($order->orderItems as $orderItem) {
                    $product = $orderItem->product;
                    if ($product) {
                        for ($i = 0; $i < $orderItem->quantity; $i++) {
                            $product->reduceIngredients();
                        }
                    }
                }
            }
            
            // Если меняем статус С любого НА "Отменен" - возвращаем ингредиенты
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
            DB::commit();
            
            $message = match(true) {
                $oldStatus === 'Отменен' && $newStatus !== 'Отменен' => 'Статус заказа изменен, ингредиенты списаны',
                $oldStatus !== 'Отменен' && $newStatus === 'Отменен' => 'Статус заказа изменен на "Отменен", ингредиенты восстановлены',
                default => 'Статус заказа успешно изменен'
            };
            
            return redirect()->route('admin.orders.index')->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('admin.orders.index')->with('error', 'Ошибка при изменении статуса: ' . $e->getMessage());
        }
    }
}
