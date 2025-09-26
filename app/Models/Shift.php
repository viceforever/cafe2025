<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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

    public function getDurationAttribute()
    {
        if ($this->start_time && $this->end_time) {
            $start = Carbon::parse($this->start_time);
            $end = Carbon::parse($this->end_time);
            
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

    public function calculateStats()
    {
        $orders = $this->orders()->get();

        DB::table('shifts')
            ->where('id', $this->id)
            ->update([
                'total_orders' => $orders->count(),
                'total_revenue' => $orders->where('payment_method', 'cash')->sum('total_amount') + $orders->where('payment_method', 'card')->sum('total_amount'),
                'cash_sales' => $orders->where('payment_method', 'cash')->sum('total_amount'),
                'card_sales' => $orders->where('payment_method', 'card')->sum('total_amount')
            ]);
    }

    public function endShift($endTime, $notes = null)
    {
        DB::table('shifts')
            ->where('id', $this->id)
            ->update([
                'end_time' => $endTime,
                'status' => 'completed',
                'notes' => $notes,
                'updated_at' => now()
            ]);
        
        $this->refresh();
        return $this;
    }
}
