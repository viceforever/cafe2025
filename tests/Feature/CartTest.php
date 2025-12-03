<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\CategoryProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Создаём пользователя и продукт для тестов
        $this->user = User::factory()->create([
            'role' => 'client'
        ]);

        $category = CategoryProduct::create([
            'name_category' => 'Пиццы',
            'description_category' => 'Вкусные пиццы'
        ]);

        $this->product = Product::create([
            'name_product' => 'Маргарита',
            'description_product' => 'Классическая пицца',
            'price_product' => 450.00,
            'id_category' => $category->id,
            'img_product' => 'margherita.jpg'
        ]);
    }

    /**
     * Проверка добавления товара в корзину
     */
    public function testUserCanAddItemToCart(): void
    {
        $response = $this->actingAs($this->user)->json('POST',
            "/cart/add/{$this->product->id}",
            ['quantity' => 2]
        );

        // Проверяем, что товар добавлен успешно (JSON ответ)
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        
        // Проверяем что товар в корзине
        $this->get('/cart')
            ->assertSee($this->product->name_product);
    }

    /**
     * Проверка изменения количества товара в корзине
     */
    public function testUserCanUpdateCartItemQuantity(): void
    {
        // Сначала добавляем товар
        $this->actingAs($this->user)->json('POST',
            "/cart/add/{$this->product->id}",
            ['quantity' => 2]
        );

        $response = $this->actingAs($this->user)->json('POST',
            "/cart/update/{$this->product->id}",
            ['action' => 'increase']
        );

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    /**
     * Проверка удаления товара из корзины
     */
    public function testUserCanRemoveItemFromCart(): void
    {
        // Добавляем товар
        $this->actingAs($this->user)->json('POST',
            "/cart/add/{$this->product->id}",
            ['quantity' => 2]
        );

        $response = $this->actingAs($this->user)->json('POST',
            "/cart/remove/{$this->product->id}"
        );

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    /**
     * Проверка что неавторизованный пользователь может просматривать корзину
     */
    public function testGuestCanViewCart(): void
    {
        $response = $this->get('/cart');

        $response->assertStatus(200);
    }
}
