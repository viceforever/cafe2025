<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\OrderStatus;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'shift_id', // добавил shift_id для связи с сменами
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

    public function shift()
    {
        return $this->belongsTo(Shift::class);
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
            OrderStatus::PENDING => 'bg-warning',
            OrderStatus::COOKING => 'bg-info',
            OrderStatus::READY => 'bg-success',
            OrderStatus::COMPLETED => 'bg-secondary',
            OrderStatus::CANCELLED => 'bg-danger',
            default => 'bg-primary'
        };
    }
}
