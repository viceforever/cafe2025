<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'start_time',
        'end_time',
        'total_orders',
        'total_revenue',
        'cash_sales',
        'card_sales',
        'notes',
        'status'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'total_revenue' => 'decimal:2',
        'cash_sales' => 'decimal:2',
        'card_sales' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getDurationAttribute()
    {
        if ($this->start_time && $this->end_time) {
            return $this->start_time->diffForHumans($this->end_time, true);
        }
        return null;
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function calculateStats()
    {
        $orders = Order::where('created_at', '>=', $this->start_time)
                      ->where('created_at', '<=', $this->end_time ?? now())
                      ->get();

        $this->total_orders = $orders->count();
        $this->total_revenue = $orders->sum('total_amount');
        $this->cash_sales = $orders->where('payment_method', 'cash')->sum('total_amount');
        $this->card_sales = $orders->where('payment_method', 'card')->sum('total_amount');
        $this->save();
    }
}
