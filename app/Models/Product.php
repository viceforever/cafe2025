<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name_product',
        'description_product',
        'price_product',
        'img_product',
        'id_category'
    ];
    
    public function category()
    {
        return $this->belongsTo(CategoryProduct::class, 'id_category');
    }

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'product_ingredients')
                    ->withPivot('quantity_needed')
                    ->withTimestamps();
    }

    public function isAvailable()
    {
        foreach ($this->ingredients as $ingredient) {
            if (!$ingredient->canMakeProduct($ingredient->pivot->quantity_needed)) {
                return false;
            }
        }
        return true;
    }

    public function isAvailableInQuantity($quantity)
    {
        foreach ($this->ingredients as $ingredient) {
            $totalNeeded = $ingredient->pivot->quantity_needed * $quantity;
            if (!$ingredient->canMakeProduct($totalNeeded)) {
                return false;
            }
        }
        return true;
    }

    public function reduceIngredients()
    {
        foreach ($this->ingredients as $ingredient) {
            $ingredient->reduceQuantity($ingredient->pivot->quantity_needed);
        }
    }

    public function restoreIngredients()
    {
        foreach ($this->ingredients as $ingredient) {
            $ingredient->restoreQuantity($ingredient->pivot->quantity_needed);
        }
    }

    public function getMaxAvailableQuantity()
    {
        if ($this->ingredients->isEmpty()) {
            return PHP_INT_MAX; // Если нет ингредиентов, считаем товар всегда доступным
        }

        $minQuantity = PHP_INT_MAX;
        
        foreach ($this->ingredients as $ingredient) {
            $availableQuantity = intval($ingredient->available_quantity / $ingredient->pivot->quantity_needed);
            if ($availableQuantity < $minQuantity) {
                $minQuantity = $availableQuantity;
            }
        }
        
        return $minQuantity === PHP_INT_MAX ? 0 : $minQuantity;
    }
}
