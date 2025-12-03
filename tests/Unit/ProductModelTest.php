<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\CategoryProduct;
use App\Models\Ingredient;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductModelTest extends TestCase
{
    use RefreshDatabase;

    private $product;
    private $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = CategoryProduct::create([
            'name_category' => 'Пиццы',
            'description_category' => 'Вкусные пиццы'
        ]);

        $this->product = Product::create([
            'name_product' => 'Маргарита',
            'description_product' => 'Классическая пицца',
            'price_product' => 450.00,
            'id_category' => $this->category->id,
            'img_product' => 'margherita.jpg'
        ]);
    }

    /**
     * Проверка что продукт относится к категории
     */
    public function testProductBelongsToCategory(): void
    {
        $this->assertEquals($this->product->category->id, $this->category->id);
        $this->assertEquals($this->product->category->name_category, 'Пиццы');
    }

    /**
     * Проверка получения всех продуктов в категории
     */
    public function testCanRetrieveProductsInCategory(): void
    {
        $products = $this->category->products;

        $this->assertContains($this->product->id, $products->pluck('id'));
    }

    /**
     * Проверка методов для управления ингредиентами
     */
    public function testProductHasIngredients(): void
    {
        // Проверяем что метод существует
        $this->assertTrue(method_exists($this->product, 'ingredients'));
    }

    /**
     * Проверка что цена корректна
     */
    public function testProductPriceIsCorrect(): void
    {
        $this->assertEquals($this->product->price_product, 450.00);
    }
}
