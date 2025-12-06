<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Ingredient;
use App\Models\Shift;
use App\Models\User;
use App\Models\Schedule;
use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ManagerController extends Controller
{
    public function dashboard()
    {
        $activeShift = Shift::where('user_id', Auth::id())
                           ->where('status', 'active')
                           ->first();

        $shiftStats = null;
        if ($activeShift) {
            // Используем метод getStats из модели Shift для устранения дублирования
            $shiftStats = $activeShift->getStats();
        }

        $today = Carbon::now('Asia/Irkutsk')->toDateString();
        $todayOrders = Order::whereDate('created_at', $today)->count();
        $todayRevenue = Order::whereDate('created_at', $today)->sum('total_amount');
        $lowStockIngredients = Ingredient::whereColumn('quantity', '<=', 'min_quantity')->count();
        $pendingOrders = Order::whereIn('status', [OrderStatus::PENDING, OrderStatus::COOKING])->count();

        // Retrieve completed shift data from session
        $completedShiftData = session('completed_shift', null);

        return view('manager.dashboard', compact(
            'activeShift', 
            'shiftStats',
            'todayOrders', 
            'todayRevenue', 
            'lowStockIngredients', 
            'pendingOrders',
            'completedShiftData'
        ));
    }

    public function startShift(Request $request)
    {
        $activeShift = Shift::where('user_id', Auth::id())
                           ->where('status', 'active')
                           ->first();

        if ($activeShift) {
            return redirect()->back()->with('error', 'У вас уже есть активная смена');
        }

        $currentTime = Carbon::now('Asia/Irkutsk');
        $todaySchedule = Schedule::where('user_id', Auth::id())
                                ->whereDate('date', $currentTime->toDateString())
                                ->where('start_time', '<=', $currentTime->format('H:i:s'))
                                ->where('end_time', '>=', $currentTime->format('H:i:s'))
                                ->first();

        if (!$todaySchedule) {
            return redirect()->back()->with('error', 'Вы не можете начать смену. У вас нет расписания на это время.');
        }

        Shift::create([
            'user_id' => Auth::id(),
            'start_time' => $currentTime,
            'status' => 'active'
        ]);

        return redirect()->back()->with('success', 'Смена начата');
    }

    public function endShift(Request $request)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
            'start_time' => 'required|date',
            'end_time' => 'required|date',
            'duration_minutes' => 'required|integer|min:0'
        ]);

        $activeShift = Shift::where('user_id', Auth::id())
                       ->where('status', 'active')
                       ->first();

        if (!$activeShift) {
            return redirect()->back()->with('error', 'Активная смена не найдена');
        }

        $startTimeLocal = Carbon::parse($request->start_time)->setTimezone('Asia/Irkutsk')->format('Y-m-d H:i:s');
        $endTimeLocal = Carbon::parse($request->end_time)->setTimezone('Asia/Irkutsk')->format('Y-m-d H:i:s');

        Log::info('[SHIFT DEBUG] Получены данные с клиента', [
            'shift_id' => $activeShift->id,
            'start_time_from_client' => $request->start_time,
            'end_time_from_client' => $request->end_time,
            'start_time_local' => $startTimeLocal,
            'end_time_local' => $endTimeLocal,
            'duration_minutes' => $request->duration_minutes,
            'notes' => $request->notes
        ]);

        // Используем метод модели вместо DB::table
        $activeShift->endShift($endTimeLocal, $request->notes);
        
        // Обновляем start_time отдельно, если он был изменен
        if ($activeShift->start_time != $startTimeLocal) {
            $activeShift->update(['start_time' => $startTimeLocal]);
        }

        Log::info('[SHIFT DEBUG] Обновили смену в БД', [
            'shift_id' => $activeShift->id
        ]);
        
        Log::info('[SHIFT DEBUG] После обновления', [
            'shift_id' => $activeShift->id,
            'start_time_after' => $activeShift->start_time,
            'end_time_after' => $activeShift->end_time,
            'status_after' => $activeShift->status,
            'duration' => $activeShift->duration
        ]);

        $completedShiftData = [
            'start_time' => $request->start_time, // ISO формат для JavaScript
            'end_time' => $request->end_time,     // ISO формат для JavaScript
            'start_time_display' => Carbon::parse($request->start_time)->setTimezone('Asia/Irkutsk')->format('H:i'),
            'end_time_display' => Carbon::parse($request->end_time)->setTimezone('Asia/Irkutsk')->format('H:i'),
            'duration' => $activeShift->duration
        ];

        return redirect()->route('manager.dashboard')
                    ->with('success', 'Смена завершена')
                    ->with('completed_shift', $completedShiftData);
    }

    public function orders(Request $request)
    {
        $query = Order::with(['user', 'orderItems.product'])
                     ->orderBy('created_at', 'desc');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Изменил пагинацию с 20 на 4 заказа
        $orders = $query->paginate(4)->appends($request->query());
        
        // Получаем все возможные статусы для фильтра из констант
        $statuses = OrderStatus::all();

        foreach ($orders as $order) {
            $order->allowedTransitions = \App\Enums\OrderStatus::$allowedTransitions[$order->status] ?? [];
        }
        return view('manager.orders.index', compact('orders', 'statuses'));
    }

    public function updateOrderStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', OrderStatus::all())
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;

        DB::beginTransaction();
        try {
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
                    return redirect()->back()->with('error', $errorMessage);
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
                    return redirect()->back()->with('error', $errorMessage);
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
                // Отменяем заказ - восстанавливаем ингредиенты
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
                $newStatus === OrderStatus::CANCELLED && OrderStatus::canRestoreIngredients($oldStatus) => 'Статус изменен на "Отменен", ингредиенты восстановлены',
                $newStatus === OrderStatus::CANCELLED => 'Статус изменен на "Отменен"',
                default => 'Статус заказа обновлен'
            };

            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ошибка при изменении статуса заказа', [
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Ошибка при изменении статуса: ' . $e->getMessage());
        }
    }

    public function confirmOrder(Order $order)
    {
        $order->update(['status' => OrderStatus::CONFIRMED]);
        
        return redirect()->back()->with('success', 'Заказ подтвержден');
    }

    public function cancelOrder(Order $order)
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
                ? 'Заказ отменен, ингредиенты восстановлены'
                : 'Заказ отменен';
            
            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ошибка при отмене заказа', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Ошибка при отмене заказа: ' . $e->getMessage());
        }
    }

    public function ingredients()
    {
        // Добавил пагинацию для ингредиентов
        $ingredients = Ingredient::orderBy('name')->paginate(4);
        $lowStockIngredients = Ingredient::whereColumn('quantity', '<=', 'min_quantity')->get();

        return view('manager.ingredients.index', compact('ingredients', 'lowStockIngredients'));
    }

    public function checkProductAvailability()
    {
        $products = Product::with('ingredients')->get();
        $availability = [];

        foreach ($products as $product) {
            $allIngredients = [];
            $missingIngredients = [];

            foreach ($product->ingredients as $ingredient) {
                $neededQuantity = $ingredient->pivot->quantity_needed;
                $canMake = $ingredient->canMakeProduct($neededQuantity);
                
                $allIngredients[] = [
                    'ingredient' => $ingredient,
                    'needed_quantity' => $neededQuantity,
                    'available_quantity' => $ingredient->quantity,
                    'unit' => $ingredient->unit,
                    'sufficient' => $canMake
                ];

                if (!$canMake) {
                    $missingIngredients[] = $ingredient;
                }
            }

            $availability[$product->id] = [
                'product' => $product,
                'available' => $product->isAvailable(),
                'missing_ingredients' => $missingIngredients,
                'all_ingredients' => $allIngredients
            ];
        }

        return view('manager.products.availability', compact('availability'));
    }

    public function getShiftStats()
    {
        $activeShift = Shift::where('user_id', Auth::id())
                           ->where('status', 'active')
                           ->first();

        if (!$activeShift) {
            return response()->json(['error' => 'Нет активной смены'], 404);
        }

        // Используем метод getStats из модели для устранения дублирования
        return response()->json($activeShift->getStats());
    }
}
