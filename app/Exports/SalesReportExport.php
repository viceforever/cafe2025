<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesReportExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data['orders']);
    }

    public function headings(): array
    {
        return [
            'ID заказа',
            'Дата',
            'Клиент',
            'Статус',
            'Способ оплаты',
            'Способ получения',
            'Сумма заказа',
            'Товары'
        ];
    }

    public function map($order): array
    {
        // исправил отображение товаров в экспорте
        $items = $order->orderItems->map(function ($item) {
            return $item->product->name_product . ' (x' . $item->quantity . ')';
        })->implode(', ');

        return [
            $order->id,
            $order->created_at->format('d.m.Y H:i'),
            // исправил отображение имени пользователя
            $order->user ? $order->user->first_name . ' ' . $order->user->last_name : 'Гость',
            $order->status,
            $order->payment_method,
            $order->delivery_method,
            number_format($order->total_amount, 2) . ' ₽',
            $items
        ];
    }
}
