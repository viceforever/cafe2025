<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Отчет по ингредиентам</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .stats { margin-bottom: 20px; }
        .stat-item { display: inline-block; margin-right: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .low-stock { background-color: #ffebee; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Отчет по ингредиентам</h1>
        <p>Период: {{ $startDate }} - {{ $endDate }}</p>
        <p>Сформирован: {{ now()->format('d.m.Y H:i') }}</p>
    </div>

    <div class="stats">
        <div class="stat-item"><strong>Всего ингредиентов:</strong> {{ $totalIngredients }}</div>
        <div class="stat-item"><strong>Низкие остатки:</strong> {{ $lowStockCount }}</div>
        <div class="stat-item"><strong>Общие затраты:</strong> {{ number_format($totalCost, 2) }} ₽</div>
        <div class="stat-item"><strong>Общее использование:</strong> {{ number_format($totalUsage, 2) }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Название</th>
                <th>Единица</th>
                <th>Остаток</th>
                <th>Мин. остаток</th>
                <th>Стоимость/ед.</th>
                <th>Использовано</th>
                <th>Затраты</th>
                <th>Статус</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ingredients as $ingredient)
            <tr class="{{ $ingredient->quantity <= $ingredient->min_quantity ? 'low-stock' : '' }}">
                <!-- исправил обращение к полю названия ингредиента -->
                <td>{{ $ingredient->name }}</td>
                <td>{{ $ingredient->unit }}</td>
                <td>{{ $ingredient->quantity }}</td>
                <td>{{ $ingredient->min_quantity }}</td>
                <td class="text-right">{{ number_format($ingredient->cost_per_unit, 2) }} ₽</td>
                <td>{{ number_format($ingredient->usage_period ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($ingredient->cost_period ?? 0, 2) }} ₽</td>
                <td>{{ $ingredient->quantity <= $ingredient->min_quantity ? 'Низкий остаток' : 'В норме' }}</td>
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
