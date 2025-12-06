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

    /**
     * Списывает ингредиенты для одного товара
     * 
     * @throws \Exception если недостаточно ингредиентов
     */
    public function reduceIngredients()
    {
        $this->reduceIngredientsInQuantity(1);
    }

    /**
     * Списывает ингредиенты для указанного количества товаров
     * 
     * @param int $quantity Количество товаров
     * @throws \Exception если недостаточно ингредиентов
     */
    public function reduceIngredientsInQuantity($quantity)
    {
        // Проверяем доступность перед списанием
        if (!$this->isAvailableInQuantity($quantity)) {
            throw new \Exception("Недостаточно ингредиентов для товара: {$this->name_product}");
        }

        foreach ($this->ingredients as $ingredient) {
            $totalNeeded = $ingredient->pivot->quantity_needed * $quantity;
            
            // Дополнительная проверка перед списанием
            if ($ingredient->quantity < $totalNeeded) {
                throw new \Exception("Недостаточно ингредиента '{$ingredient->name}'. Требуется: {$totalNeeded} {$ingredient->unit}, доступно: {$ingredient->quantity} {$ingredient->unit}");
            }
            
            $ingredient->reduceQuantity($totalNeeded);
        }
    }

    /**
     * Восстанавливает ингредиенты для одного товара
     */
    public function restoreIngredients()
    {
        $this->restoreIngredientsInQuantity(1);
    }

    /**
     * Восстанавливает ингредиенты для указанного количества товаров
     * 
     * @param int $quantity Количество товаров
     */
    public function restoreIngredientsInQuantity($quantity)
    {
        foreach ($this->ingredients as $ingredient) {
            $totalNeeded = $ingredient->pivot->quantity_needed * $quantity;
            $ingredient->restoreQuantity($totalNeeded);
        }
    }

    public function getMaxAvailableQuantity()
    {
        if ($this->ingredients->isEmpty()) {
            return PHP_INT_MAX; // Если нет ингредиентов, считаем товар всегда доступным
        }

        $minQuantity = PHP_INT_MAX;
        
        foreach ($this->ingredients as $ingredient) {
            // Исправлено: используем quantity вместо несуществующего available_quantity
            $availableQuantity = intval($ingredient->quantity / $ingredient->pivot->quantity_needed);
            if ($availableQuantity < $minQuantity) {
                $minQuantity = $availableQuantity;
            }
        }
        
        return $minQuantity === PHP_INT_MAX ? 0 : $minQuantity;
    }
}
