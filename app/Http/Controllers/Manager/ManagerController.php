<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Ingredient;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ManagerController extends Controller
{
    public function dashboard()
    {
        $activeShift = Shift::where('user_id', Auth::id())
                           ->where('status', 'active')
                           ->first();

        $todayOrders = Order::whereDate('created_at', today())->count();
        $todayRevenue = Order::whereDate('created_at', today())->sum('total_amount');
        $lowStockIngredients = Ingredient::whereRaw('quantity <= min_quantity')->count();
        $pendingOrders = Order::whereIn('status', ['В обработке', 'Готовится'])->count();

        return view('manager.dashboard', compact(
            'activeShift', 
            'todayOrders', 
            'todayRevenue', 
            'lowStockIngredients', 
            'pendingOrders'
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

        Shift::create([
            'user_id' => Auth::id(),
            'start_time' => now(),
            'status' => 'active'
        ]);

        return redirect()->back()->with('success', 'Смена начата');
    }

    public function endShift(Request $request)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000'
        ]);

        $activeShift = Shift::where('user_id', Auth::id())
                           ->where('status', 'active')
                           ->first();

        if (!$activeShift) {
            return redirect()->back()->with('error', 'Активная смена не найдена');
        }

        $activeShift->end_time = now();
        $activeShift->status = 'completed';
        $activeShift->notes = $request->notes;
        $activeShift->calculateStats();

        return redirect()->route('manager.shifts.show', $activeShift)
                        ->with('success', 'Смена завершена');
    }

    public function orders()
    {
        $orders = Order::with(['user', 'orderItems.product'])
                      ->orderBy('created_at', 'desc')
                      ->paginate(20);

        return view('manager.orders.index', compact('orders'));
    }

    public function updateOrderStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:В обработке,Готовится,Готов к выдаче,Выдан,Отменен'
        ]);

        $order->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Статус заказа обновлен');
    }

    public function ingredients()
    {
        $ingredients = Ingredient::orderBy('name')->get();
        $lowStockIngredients = $ingredients->filter(function ($ingredient) {
            return $ingredient->isLowStock();
        });

        return view('manager.ingredients.index', compact('ingredients', 'lowStockIngredients'));
    }

    public function checkProductAvailability()
    {
        $products = Product::with('ingredients')->get();
        $availability = [];

        foreach ($products as $product) {
            $availability[$product->id] = [
                'product' => $product,
                'available' => $product->isAvailable(),
                'missing_ingredients' => []
            ];

            foreach ($product->ingredients as $ingredient) {
                if (!$ingredient->canMakeProduct($ingredient->pivot->quantity_needed)) {
                    $availability[$product->id]['missing_ingredients'][] = $ingredient;
                }
            }
        }

        return view('manager.products.availability', compact('availability'));
    }
}
