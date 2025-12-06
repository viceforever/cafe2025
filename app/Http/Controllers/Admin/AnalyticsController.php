<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Ingredient;
use App\Models\User;
use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDate = $this->getStartDate($period);
        $endDate = now();

        // Основная статистика
        $totalRevenue = $this->getTotalRevenue($startDate, $endDate);
        $totalOrders = $this->getTotalOrders($startDate, $endDate);
        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        $totalCosts = $this->getTotalCosts($startDate, $endDate);
        $profit = $totalRevenue - $totalCosts;

        // Статистика по способам оплаты
        $paymentStats = $this->getPaymentStats($startDate, $endDate);

        // Статистика по способам получения
        $deliveryStats = $this->getDeliveryStats($startDate, $endDate);

        // Популярные товары
        $popularProducts = $this->getPopularProducts($startDate, $endDate, 10);

        // Выручка по дням
        $dailyRevenue = $this->getDailyRevenue($startDate, $endDate);

        // Статистика по статусам заказов
        $orderStatusStats = $this->getOrderStatusStats($startDate, $endDate);

        return view('admin.analytics.index', compact(
            'period',
            'startDate',
            'endDate',
            'totalRevenue',
            'totalOrders',
            'averageOrderValue',
            'totalCosts',
            'profit',
            'paymentStats',
            'deliveryStats',
            'popularProducts',
            'dailyRevenue',
            'orderStatusStats'
        ));
    }

    public function products(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDate = $this->getStartDate($period);
        $endDate = now();

        $popularProducts = $this->getPopularProducts($startDate, $endDate, 50);
        $revenueByProduct = $this->getRevenueByProduct($startDate, $endDate);

        return view('admin.analytics.products', compact(
            'period',
            'startDate',
            'endDate',
            'popularProducts',
            'revenueByProduct'
        ));
    }

    public function ingredients(Request $request)
    {
        $lowStockIngredients = Ingredient::whereColumn('quantity', '<=', 'min_quantity')->get();
        $expensiveIngredients = Ingredient::orderBy('cost_per_unit', 'desc')->take(10)->get();
        $ingredientUsage = $this->getIngredientUsage();

        return view('admin.analytics.ingredients', compact(
            'lowStockIngredients',
            'expensiveIngredients',
            'ingredientUsage'
        ));
    }

    public function financial(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDate = $this->getStartDate($period);
        $endDate = now();

        $monthlyRevenue = $this->getMonthlyRevenue();
        $costBreakdown = $this->getCostBreakdown($startDate, $endDate);
        $profitMargin = $this->getProfitMargin($startDate, $endDate);

        return view('admin.analytics.financial', compact(
            'period',
            'startDate',
            'endDate',
            'monthlyRevenue',
            'costBreakdown',
            'profitMargin'
        ));
    }

    private function getStartDate($period)
    {
        return match($period) {
            'today' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };
    }

    private function getTotalRevenue($startDate, $endDate)
    {
        return Order::whereBetween('created_at', [$startDate, $endDate])
                   ->whereNotIn('status', [OrderStatus::CANCELLED])
                   ->sum('total_amount');
    }

    private function getTotalOrders($startDate, $endDate)
    {
        return Order::whereBetween('created_at', [$startDate, $endDate])
                   ->whereNotIn('status', [OrderStatus::CANCELLED])
                   ->count();
    }

    /**
     * Проверяет, нужно ли учитывать затраты для заказа
     * 
     * Затраты учитываются для:
     * 1. Неотмененных заказов (ингредиенты использованы, выручка получена)
     * 2. Отмененных заказов в поздних статусах (ингредиенты использованы, выручка не получена)
     * 
     * Затраты НЕ учитываются для:
     * - Отмененных заказов в ранних статусах (ингредиенты возвращены)
     * 
     * @param Order $order
     * @return bool
     */
    private function shouldIncludeOrderInCosts(Order $order): bool
    {
        // Неотмененные заказы всегда учитываются
        if ($order->status !== OrderStatus::CANCELLED) {
            return true;
        }
        
        // Для отмененных заказов без истории статусов мы не можем точно определить,
        // были ли ингредиенты использованы. Используем консервативный подход:
        // не учитываем затраты для отмененных заказов.
        // 
        // ПРИМЕЧАНИЕ: Это может занижать затраты, если заказ был отменен
        // в статусе "Готовится" или позже (ингредиенты уже использованы).
        // Для точного учета необходимо добавить поле в БД для отслеживания
        // использования ингредиентов или историю статусов.
        
        return false;
    }

    private function getTotalCosts($startDate, $endDate)
    {
        // Расчет затрат на основе использованных ингредиентов
        // Учитываем затраты для всех неотмененных заказов
        // 
        // ПРИМЕЧАНИЕ: Отмененные заказы в поздних статусах (где ингредиенты уже использованы)
        // не учитываются, так как у нас нет истории статусов для определения,
        // был ли заказ в статусе "Готовится" или позже перед отменой.
        // Это может занижать затраты. Для точного учета необходимо добавить
        // поле в БД для отслеживания использования ингредиентов.
        $orderItems = OrderItem::whereHas('order', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate])
                  ->whereNotIn('status', [OrderStatus::CANCELLED]);
        })->with('product.ingredients')->get();

        $totalCosts = 0;
        foreach ($orderItems as $orderItem) {
            foreach ($orderItem->product->ingredients as $ingredient) {
                $usedQuantity = $ingredient->pivot->quantity_needed * $orderItem->quantity;
                $cost = $usedQuantity * $ingredient->cost_per_unit;
                $totalCosts += $cost;
            }
        }

        return $totalCosts;
    }

    private function getPaymentStats($startDate, $endDate)
    {
        return Order::whereBetween('created_at', [$startDate, $endDate])
                   ->whereNotIn('status', [OrderStatus::CANCELLED])
                   ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
                   ->groupBy('payment_method')
                   ->get();
    }

    private function getDeliveryStats($startDate, $endDate)
    {
        return Order::whereBetween('created_at', [$startDate, $endDate])
                   ->whereNotIn('status', [OrderStatus::CANCELLED])
                   ->select('delivery_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
                   ->groupBy('delivery_method')
                   ->get();
    }

    private function getPopularProducts($startDate, $endDate, $limit = 10)
    {
        return OrderItem::whereHas('order', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate])
                  ->whereNotIn('status', [OrderStatus::CANCELLED]);
        })
        ->select('product_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('COUNT(*) as order_count'))
        ->with('product')
        ->groupBy('product_id')
        ->orderBy('total_quantity', 'desc')
        ->take($limit)
        ->get();
    }

    private function getRevenueByProduct($startDate, $endDate)
    {
        return OrderItem::whereHas('order', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate])
                  ->whereNotIn('status', [OrderStatus::CANCELLED]);
        })
        ->select('product_id', DB::raw('SUM(quantity * price) as total_revenue'), DB::raw('SUM(quantity) as total_quantity'))
        ->with('product')
        ->groupBy('product_id')
        ->orderBy('total_revenue', 'desc')
        ->get();
    }

    private function getDailyRevenue($startDate, $endDate)
    {
        return Order::whereBetween('created_at', [$startDate, $endDate])
                   ->whereNotIn('status', [OrderStatus::CANCELLED])
                   ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as revenue'), DB::raw('COUNT(*) as orders'))
                   ->groupBy('date')
                   ->orderBy('date')
                   ->get();
    }

    private function getOrderStatusStats($startDate, $endDate)
    {
        return Order::whereBetween('created_at', [$startDate, $endDate])
                   ->select('status', DB::raw('COUNT(*) as count'))
                   ->groupBy('status')
                   ->get();
    }

    private function getMonthlyRevenue()
    {
        return Order::whereNotIn('status', [OrderStatus::CANCELLED])
                   ->select(DB::raw('YEAR(created_at) as year'), DB::raw('MONTH(created_at) as month'), DB::raw('SUM(total_amount) as revenue'))
                   ->groupBy('year', 'month')
                   ->orderBy('year', 'desc')
                   ->orderBy('month', 'desc')
                   ->take(12)
                   ->get();
    }

    private function getCostBreakdown($startDate, $endDate)
    {
        $ingredients = Ingredient::all();
        $breakdown = [];

        foreach ($ingredients as $ingredient) {
            $usedQuantity = $this->getIngredientUsageInPeriod($ingredient->id, $startDate, $endDate);
            $cost = $usedQuantity * $ingredient->cost_per_unit;
            
            if ($cost > 0) {
                $breakdown[] = [
                    'ingredient' => $ingredient,
                    'used_quantity' => $usedQuantity,
                    'cost' => $cost
                ];
            }
        }

        return collect($breakdown)->sortByDesc('cost');
    }

    private function getIngredientUsageInPeriod($ingredientId, $startDate, $endDate)
    {
        $orderItems = OrderItem::whereHas('order', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate])
                  ->whereNotIn('status', [OrderStatus::CANCELLED]);
        })->with('product.ingredients')->get();

        $totalUsage = 0;
        foreach ($orderItems as $orderItem) {
            $ingredient = $orderItem->product->ingredients->firstWhere('id', $ingredientId);
            if ($ingredient) {
                $totalUsage += $ingredient->pivot->quantity_needed * $orderItem->quantity;
            }
        }

        return $totalUsage;
    }

    private function getProfitMargin($startDate, $endDate)
    {
        $revenue = $this->getTotalRevenue($startDate, $endDate);
        $costs = $this->getTotalCosts($startDate, $endDate);
        
        return $revenue > 0 ? (($revenue - $costs) / $revenue) * 100 : 0;
    }

    private function getIngredientUsage()
    {
        return Ingredient::select('ingredients.*', DB::raw('
            (SELECT SUM(oi.quantity * pi.quantity_needed) 
             FROM order_items oi 
             JOIN product_ingredients pi ON oi.product_id = pi.product_id 
             JOIN orders o ON oi.order_id = o.id 
             WHERE pi.ingredient_id = ingredients.id 
             AND o.status != "' . OrderStatus::CANCELLED . '"
             AND o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ) as usage_last_30_days
        '))->get();
    }
}
