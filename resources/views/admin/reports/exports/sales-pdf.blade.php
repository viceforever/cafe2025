<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Отчет по продажам</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .stats { margin-bottom: 20px; }
        .stat-item { display: inline-block; margin-right: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Отчет по продажам</h1>
        <p>Период: {{ $startDate }} - {{ $endDate }}</p>
        <p>Сформирован: {{ now()->format('d.m.Y H:i') }}</p>
    </div>

    <div class="stats">
        <div class="stat-item"><strong>Общая выручка:</strong> {{ number_format($totalRevenue, 2) }} ₽</div>
        <div class="stat-item"><strong>Количество заказов:</strong> {{ $totalOrders }}</div>
        <div class="stat-item"><strong>Средний чек:</strong> {{ number_format($averageOrderValue, 2) }} ₽</div>
    </div>

    @if(count($productStats) > 0)
    <h3>Статистика по товарам</h3>
    <table>
        <thead>
            <tr>
                <th>Товар</th>
                <th>Категория</th>
                <th>Продано</th>
                <th>Выручка</th>
                <th>Заказов</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productStats as $stat)
            <tr>
                <!-- исправил обращение к полям продукта -->
                <td>{{ $stat['product']->name_product }}</td>
                <td>{{ $stat['product']->category->name_category ?? 'Без категории' }}</td>
                <td>{{ $stat['quantity'] }}</td>
                <td class="text-right">{{ number_format($stat['revenue'], 2) }} ₽</td>
                <td>{{ $stat['orders_count'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <h3>Заказы</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Дата</th>
                <th>Клиент</th>
                <th>Телефон</th>
                <th>Статус</th>
                <th>Сумма</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            <tr>
                <td>{{ $order->id }}</td>
                <td>{{ $order->created_at->format('d.m.Y H:i') }}</td>
                <!-- исправил отображение имени клиента -->
                <td>{{ $order->user ? $order->user->first_name . ' ' . $order->user->last_name : 'Гость' }}</td>
                <td>{{ $order->user ? $order->user->phone : '' }}</td>
                <td>{{ $order->status }}</td>
                <td class="text-right">{{ number_format($order->total_amount, 2) }} ₽</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- добавил кнопку печати -->
    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">
            Печать отчета
        </button>
    </div>
</body>
</html>
