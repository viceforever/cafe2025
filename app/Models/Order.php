<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'total_amount', 
        'status', 
        'payment_method', 
        'delivery_method', 
        'delivery_address', 
        'phone', 
        'notes'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getPaymentMethodTextAttribute()
    {
        return $this->payment_method === 'cash' ? 'Наличными' : 'Картой';
    }

    public function getDeliveryMethodTextAttribute()
    {
        return $this->delivery_method === 'pickup' ? 'Самовывоз' : 'Доставка';
    }

    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'В обработке' => 'bg-warning',
            'Готовится' => 'bg-info',
            'Готов к выдаче' => 'bg-success',
            'Выдан' => 'bg-secondary',
            'Отменен' => 'bg-danger',
            default => 'bg-primary'
        };
    }
}
