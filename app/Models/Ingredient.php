<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'unit',
        'quantity',
        'cost_per_unit',
        'min_quantity'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'cost_per_unit' => 'decimal:2',
        'min_quantity' => 'decimal:2',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_ingredients')
                    ->withPivot('quantity_needed')
                    ->withTimestamps();
    }

    public function isLowStock()
    {
        return $this->quantity <= $this->min_quantity;
    }

    public function canMakeProduct($quantityNeeded)
    {
        return $this->quantity >= $quantityNeeded;
    }

    public function reduceQuantity($amount)
    {
        $this->quantity -= $amount;
        $this->save();
    }
}
