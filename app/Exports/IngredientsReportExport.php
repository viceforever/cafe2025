<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class IngredientsReportExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data['ingredients']);
    }

    public function headings(): array
    {
        return [
            'Название ингредиента',
            'Единица измерения',
            'Текущий остаток',
            'Минимальный остаток',
            'Стоимость за единицу',
            'Использовано за период',
            'Стоимость использования',
            'Статус остатка'
        ];
    }

    public function map($ingredient): array
    {
        $status = $ingredient->quantity <= $ingredient->min_quantity ? 'Низкий остаток' : 'В норме';

        return [
            $ingredient->name,
            $ingredient->unit,
            $ingredient->quantity,
            $ingredient->min_quantity,
            number_format($ingredient->cost_per_unit, 2) . ' ₽',
            $ingredient->usage_period ?? 0,
            number_format($ingredient->cost_period ?? 0, 2) . ' ₽',
            $status
        ];
    }
}
