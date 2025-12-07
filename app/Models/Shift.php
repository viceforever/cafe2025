<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\OrderStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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

    public function getActiveOrdersAttribute()
    {
        if ($this->relationLoaded('orders')) {
            return $this->orders->where('status', '!=', OrderStatus::CANCELLED);
        }
        return collect();
    }


    public function getDurationAttribute()
    {
        $startTime = $this->start_time;
        $endTime = $this->end_time;
        
        if ($startTime && $endTime) {
            $start = Carbon::parse($startTime);
            $end = Carbon::parse($endTime);
            
            $diffInMinutes = $start->diffInMinutes($end);
            $hours = intval($diffInMinutes / 60);
            $minutes = $diffInMinutes % 60;
            
            if ($hours > 0) {
                return $hours . ' ч ' . $minutes . ' мин';
            } else {
                return $minutes . ' мин';
            }
        }
        return null;
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Получить статистику по смене
     * 
     * @return array
     */
    public function getStats()
    {
        $orders = $this->orders()->where('status', '!=', OrderStatus::CANCELLED)->get();
        
        return [
            'orders_count' => $orders->count(),
            'total_revenue' => $orders->sum('total_amount'),
            'cash_revenue' => $orders->where('payment_method', 'cash')->sum('total_amount'),
            'card_revenue' => $orders->where('payment_method', 'card')->sum('total_amount'),
            'completed_orders' => $orders->where('status', OrderStatus::COMPLETED)->count(),
            'pending_orders' => $orders->whereIn('status', [
                OrderStatus::PENDING,
                OrderStatus::CONFIRMED,
                OrderStatus::COOKING,
                OrderStatus::READY
            ])->count()
        ];
    }

    /**
     * Рассчитать и сохранить статистику смены
     */
    public function calculateStats()
    {
        $stats = $this->getStats();
        
        $this->update([
            'total_orders' => $stats['orders_count'],
            'total_revenue' => $stats['total_revenue'],
            'cash_sales' => $stats['cash_revenue'],
            'card_sales' => $stats['card_revenue']
        ]);
    }

    /**
     * Завершить смену
     * 
     * @param string $endTime
     * @param string|null $notes
     * @return $this
     */
    public function endShift($endTime, $notes = null)
    {
        $this->update([
            'end_time' => $endTime,
            'status' => 'completed',
            'notes' => $notes
        ]);
        
        return $this;
    }
}