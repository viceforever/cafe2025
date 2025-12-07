<?php

namespace App\Http\Controllers;
use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $statuses = OrderStatus::all();
        
        foreach ($orders as $order) {
            $order->allowedTransitions = \App\Enums\OrderStatus::$allowedTransitions[$order->status] ?? [];
        }
        return view('admin.orders.index', compact('orders', 'statuses'));
    }
    
    public function confirm(Order $order)
    {
        $order->update(['status' => OrderStatus::CONFIRMED]);
        return redirect()->route('admin.orders.index')->with('success', 'Заказ успешно подтвержден');
    }

    public function cancel(Order $order)
    {
        DB::beginTransaction();
        try {
            $oldStatus = $order->status;
            
            if ($order->status !== OrderStatus::CANCELLED) {
                if (OrderStatus::canRestoreIngredients($order->status)) {
                    foreach ($order->orderItems as $orderItem) {
                        $product = $orderItem->product;
                        if ($product) {
                            $product->restoreIngredientsInQuantity($orderItem->quantity);
                        }
                    }
                }
            }
            
            $order->update(['status' => OrderStatus::CANCELLED]);
            DB::commit();
            
            $message = OrderStatus::canRestoreIngredients($oldStatus)
                ? 'Заказ успешно отменен, ингредиенты восстановлены'
                : 'Заказ успешно отменен';
            
            return redirect()->route('admin.orders.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ошибка при отмене заказа', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('admin.orders.index')->with('error', 'Ошибка при отмене заказа: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', OrderStatus::all())
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;

        DB::beginTransaction();
        try {
            // Проверка допустимого перехода статусов (state machine)
            if (!OrderStatus::isAllowedTransition($oldStatus, $newStatus)) {
                DB::rollBack();
                return redirect()->route('admin.orders.index')->with('error', 'Недопустимый переход статуса заказа!');
            }

            // Загружаем связанные данные для предотвращения N+1
            $order->load('orderItems.product.ingredients');
            
            // Восстановление заказа из CANCELLED
            // Ингредиенты могли быть восстановлены при отмене (если отмена была из PENDING/CONFIRMED)
            // Поэтому нужно проверить доступность и списать ингредиенты
            if ($oldStatus === OrderStatus::CANCELLED && $newStatus !== OrderStatus::CANCELLED) {
                // Сначала проверяем доступность
                $unavailableProducts = [];
                foreach ($order->orderItems as $orderItem) {
                    $product = $orderItem->product;
                    if ($product && !$product->isAvailableInQuantity($orderItem->quantity)) {
                        $unavailableProducts[] = $product->name_product;
                    }
                }
                
                if (!empty($unavailableProducts)) {
                    DB::rollBack();
                    $errorMessage = 'Невозможно восстановить заказ. Недостаточно ингредиентов для товаров: ' . implode(', ', $unavailableProducts);
                    return redirect()->route('admin.orders.index')->with('error', $errorMessage);
                }
                
                // Если доступность подтверждена, списываем ингредиенты
                foreach ($order->orderItems as $orderItem) {
                    $product = $orderItem->product;
                    if ($product) {
                        $product->reduceIngredientsInQuantity($orderItem->quantity);
                    }
                }
            }

            // Переход в статус COOKING - проверяем доступность ингредиентов
            // Ингредиенты уже списаны при создании заказа, но нужно убедиться, что их достаточно
            if ($newStatus === OrderStatus::COOKING && $oldStatus !== OrderStatus::COOKING) {
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
                    DB::rollBack();
                    $errorMessage = 'Невозможно начать готовку. Недостаточно ингредиентов: ' . implode(', ', $missingIngredients);
                    return redirect()->route('admin.orders.index')->with('error', $errorMessage);
                }
            }
            
            // Откат из COOKING в более ранние статусы - восстанавливаем ингредиенты
            // (так как готовка отменена, ингредиенты еще не использованы)
            if (in_array($oldStatus, [OrderStatus::COOKING, OrderStatus::READY]) 
                && in_array($newStatus, [OrderStatus::PENDING, OrderStatus::CONFIRMED])) {
                foreach ($order->orderItems as $orderItem) {
                    $product = $orderItem->product;
                    if ($product) {
                        $product->restoreIngredientsInQuantity($orderItem->quantity);
                    }
                }
            }
            
            // Отмена заказа - восстанавливаем ингредиенты только если они еще не использованы
            if ($newStatus === OrderStatus::CANCELLED && OrderStatus::canRestoreIngredients($oldStatus)) {
                foreach ($order->orderItems as $orderItem) {
                    $product = $orderItem->product;
                    if ($product) {
                        $product->restoreIngredientsInQuantity($orderItem->quantity);
                    }
                }
            }

            $order->update(['status' => $newStatus]);
            DB::commit();
            
            $message = match(true) {
                $oldStatus === OrderStatus::CANCELLED && $newStatus !== OrderStatus::CANCELLED => 'Статус изменен, ингредиенты зарезервированы',
                $newStatus === OrderStatus::COOKING && $oldStatus !== OrderStatus::COOKING => 'Статус изменен на "Готовится"',
                $newStatus === OrderStatus::CANCELLED && OrderStatus::canRestoreIngredients($oldStatus) => 'Статус изменен на "Отменен", ингредиенты восстановлены',
                $newStatus === OrderStatus::CANCELLED => 'Статус изменен на "Отменен"',
                default => 'Статус заказа успешно изменен'
            };
            
            return redirect()->route('admin.orders.index')->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ошибка при изменении статуса заказа', [
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('admin.orders.index')->with('error', 'Ошибка при изменении статуса: ' . $e->getMessage());
        }
    }
}
