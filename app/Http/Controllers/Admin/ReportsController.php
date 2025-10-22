<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Ingredient;
use App\Models\CategoryProduct;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class ReportsController extends Controller
{
    public function index()
    {
        $todayOrders = Order::whereDate('created_at', today())->count();
        $monthlyRevenue = Order::whereMonth('created_at', now()->month)
                              ->whereYear('created_at', now()->year)
                              ->sum('total_amount');
        $lowStockIngredients = Ingredient::whereRaw('quantity <= min_quantity')->count();
        
        return view('admin.reports.index', compact('todayOrders', 'monthlyRevenue', 'lowStockIngredients'));
    }

    public function salesReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $categoryId = $request->get('category_id');
        $productId = $request->get('product_id');

        $query = Order::whereBetween('created_at', [$startDate, $endDate])
                     ->whereNotIn('status', ['Отменен'])
                     ->with(['orderItems' => function($q) {
                         $q->with(['product' => function($pq) {
                             $pq->with('category');
                         }]);
                     }, 'user:id,first_name,last_name,phone']); // исправил поля пользователя

        // Фильтр по категории
        if ($categoryId) {
            $query->whereHas('orderItems.product', function ($q) use ($categoryId) {
                $q->where('id_category', $categoryId);
            });
        }

        // Фильтр по товару
        if ($productId) {
            $query->whereHas('orderItems', function ($q) use ($productId) {
                $q->where('product_id', $productId);
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(50);

        $stats = Order::whereBetween('created_at', [$startDate, $endDate])
                     ->whereNotIn('status', ['Отменен'])
                     ->selectRaw('COUNT(*) as total_orders, SUM(total_amount) as total_revenue')
                     ->first();

        $totalRevenue = $stats->total_revenue ?? 0;
        $totalOrders = $stats->total_orders ?? 0;
        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        $productStats = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereNotIn('orders.status', ['Отменен'])
            ->select(
                'products.id',
                'products.name_product',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue'),
                DB::raw('COUNT(DISTINCT orders.id) as orders_count')
            )
            ->groupBy('products.id', 'products.name_product')
            ->orderBy('total_revenue', 'desc')
            ->get();

        $categories = CategoryProduct::select('id', 'name_category')->get();
        $products = Product::select('id', 'name_product')->get();

        return view('admin.reports.sales', compact(
            'orders', 'startDate', 'endDate', 'categoryId', 'productId',
            'totalRevenue', 'totalOrders', 'averageOrderValue', 'productStats',
            'categories', 'products'
        ));
    }

    public function ingredientsReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $lowStock = $request->get('low_stock', false);
        $minQuantity = $request->get('min_quantity', 0);

        $query = Ingredient::query();

        // Фильтр по низким остаткам
        if ($lowStock) {
            $query->whereRaw('quantity <= min_quantity');
        }

        // Фильтр по минимальному количеству
        if ($minQuantity > 0) {
            $query->where('quantity', '>=', $minQuantity);
        }

        $ingredients = $query->get();

        $usageData = $this->getIngredientsUsageBatch($ingredients->pluck('id'), $startDate, $endDate);

        foreach ($ingredients as $ingredient) {
            $usage = $usageData[$ingredient->id] ?? 0;
            $ingredient->setAttribute('usage_period', (float) $usage);
            $ingredient->setAttribute('cost_period', (float) $usage * $ingredient->cost_per_unit);
        }

        // Общая статистика
        $totalIngredients = $ingredients->count();
        $lowStockCount = $ingredients->filter(function($ing) {
            return $ing->quantity <= $ing->min_quantity;
        })->count();
        $totalCost = $ingredients->sum(function($ing) {
            return $ing->getAttribute('cost_period') ?? 0;
        });
        $totalUsage = $ingredients->sum(function($ing) {
            return $ing->getAttribute('usage_period') ?? 0;
        });

        return view('admin.reports.ingredients', compact(
            'ingredients', 'startDate', 'endDate', 'lowStock', 'minQuantity',
            'totalIngredients', 'lowStockCount', 'totalCost', 'totalUsage'
        ));
    }

    public function exportSalesReport(Request $request, $format)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $categoryId = $request->get('category_id');
        $productId = $request->get('product_id');

        $data = $this->getSalesReportData($startDate, $endDate, $categoryId, $productId);

        if ($format === 'pdf') {
            return view('admin.reports.exports.sales-pdf', $data);
        }

        if ($format === 'excel') {
            return $this->exportSalesReportCSV($data, $startDate, $endDate);
        }

        return redirect()->back()->with('error', 'Неподдерживаемый формат экспорта');
    }

    public function exportIngredientsReport(Request $request, $format)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $lowStock = $request->get('low_stock', false);
        $minQuantity = $request->get('min_quantity', 0);

        $data = $this->getIngredientsReportData($startDate, $endDate, $lowStock, $minQuantity);

        if ($format === 'pdf') {
            return view('admin.reports.exports.ingredients-pdf', $data);
        }

        if ($format === 'excel') {
            return $this->exportIngredientsReportCSV($data, $startDate, $endDate);
        }

        return redirect()->back()->with('error', 'Неподдерживаемый формат экспорта');
    }

    private function getSalesReportData($startDate, $endDate, $categoryId = null, $productId = null)
    {
        $query = Order::whereBetween('created_at', [$startDate, $endDate])
                     ->whereNotIn('status', ['Отменен'])
                     ->with(['orderItems' => function($q) {
                         $q->with(['product' => function($pq) {
                             $pq->with('category');
                         }]);
                     }, 'user:id,first_name,last_name,phone']); // исправил поля пользователя

        if ($categoryId) {
            $query->whereHas('orderItems.product', function ($q) use ($categoryId) {
                $q->where('id_category', $categoryId);
            });
        }

        if ($productId) {
            $query->whereHas('orderItems', function ($q) use ($productId) {
                $q->where('product_id', $productId);
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        $totalRevenue = $orders->sum('total_amount');
        $totalOrders = $orders->count();
        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        $productStats = [];
        foreach ($orders as $order) {
            foreach ($order->orderItems as $item) {
                $productId = $item->product_id;
                if (!isset($productStats[$productId])) {
                    $productStats[$productId] = [
                        'product' => $item->product,
                        'quantity' => 0,
                        'revenue' => 0,
                        'orders_count' => 0
                    ];
                }
                $productStats[$productId]['quantity'] += $item->quantity;
                $productStats[$productId]['revenue'] += $item->quantity * $item->price;
                $productStats[$productId]['orders_count']++;
            }
        }

        return compact('orders', 'startDate', 'endDate', 'totalRevenue', 'totalOrders', 'averageOrderValue', 'productStats');
    }

    private function getIngredientsReportData($startDate, $endDate, $lowStock = false, $minQuantity = 0)
    {
        $query = Ingredient::query();

        if ($lowStock) {
            $query->whereRaw('quantity <= min_quantity');
        }

        if ($minQuantity > 0) {
            $query->where('quantity', '>=', $minQuantity);
        }

        $ingredients = $query->get();

        $usageData = $this->getIngredientsUsageBatch($ingredients->pluck('id'), $startDate, $endDate);

        foreach ($ingredients as $ingredient) {
            $usage = $usageData[$ingredient->id] ?? 0;
            $ingredient->setAttribute('usage_period', (float) $usage);
            $ingredient->setAttribute('cost_period', (float) $usage * $ingredient->cost_per_unit);
        }

        $totalIngredients = $ingredients->count();
        $lowStockCount = $ingredients->filter(function($ing) {
            return $ing->quantity <= $ing->min_quantity;
        })->count();
        $totalCost = $ingredients->sum(function($ing) {
            return $ing->getAttribute('cost_period') ?? 0;
        });
        $totalUsage = $ingredients->sum(function($ing) {
            return $ing->getAttribute('usage_period') ?? 0;
        });

        return compact('ingredients', 'startDate', 'endDate', 'totalIngredients', 'lowStockCount', 'totalCost', 'totalUsage');
    }

    private function getIngredientUsageInPeriod($ingredientId, $startDate, $endDate)
    {
        $orderItems = OrderItem::whereHas('order', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate])
                  ->whereNotIn('status', ['Отменен']);
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

    private function getIngredientsUsageBatch($ingredientIds, $startDate, $endDate)
    {
        $usageData = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('product_ingredients', 'order_items.product_id', '=', 'product_ingredients.product_id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereNotIn('orders.status', ['Отменен'])
            ->whereIn('product_ingredients.ingredient_id', $ingredientIds)
            ->select(
                'product_ingredients.ingredient_id',
                DB::raw('SUM(product_ingredients.quantity_needed * order_items.quantity) as total_usage')
            )
            ->groupBy('product_ingredients.ingredient_id')
            ->get()
            ->pluck('total_usage', 'ingredient_id')
            ->toArray();

        return $usageData;
    }

    private function exportSalesReportCSV($data, $startDate, $endDate)
    {
        $filename = 'sales-report-' . $startDate . '-to-' . $endDate . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Заголовки CSV
            fputcsv($file, ['№ заказа', 'Дата', 'Клиент', 'Телефон', 'Статус', 'Сумма', 'Товары'], ';');
            
            foreach ($data['orders'] as $order) {
                $items = $order->orderItems->map(function($item) {
                    return $item->product->name_product . ' x' . $item->quantity;
                })->implode('; ');
                
                $customerName = $order->user ? $order->user->first_name . ' ' . $order->user->last_name : 'Гость';
                $customerPhone = $order->user ? $order->user->phone : '';
                
                fputcsv($file, [
                    $order->id,
                    $order->created_at->format('d.m.Y H:i'),
                    $customerName,
                    $customerPhone,
                    $order->status,
                    number_format($order->total_amount, 2),
                    $items
                ], ';');
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportIngredientsReportCSV($data, $startDate, $endDate)
    {
        $filename = 'ingredients-report-' . $startDate . '-to-' . $endDate . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Заголовки CSV
            fputcsv($file, ['Ингредиент', 'Текущий остаток', 'Мин. остаток', 'Единица измерения', 'Стоимость за единицу', 'Использовано за период', 'Стоимость использования'], ';');
            
            foreach ($data['ingredients'] as $ingredient) {
                $usagePeriod = (float) ($ingredient->getAttribute('usage_period') ?? 0);
                $costPeriod = (float) ($ingredient->getAttribute('cost_period') ?? 0);
                
                fputcsv($file, [
                    $ingredient->name,
                    $ingredient->quantity,
                    $ingredient->min_quantity,
                    $ingredient->unit,
                    number_format($ingredient->cost_per_unit, 2),
                    number_format($usagePeriod, 2),
                    number_format($costPeriod, 2)
                ], ';');
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
