<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\CategoryProduct;
use App\Models\Order;
use App\Models\Shift;
use App\Models\Ingredient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Позитивные тесты - проверка корректной работы функционала
 */
class PositiveTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $client;
    private $product;
    private $category;
    private $shift;
    private $ingredient1;
    private $ingredient2;

    protected function setUp(): void
    {
        parent::setUp();

        // Создаём администратора
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'first_name' => 'Admin',
            'last_name' => 'User'
        ]);

        // Создаём клиента
        $this->client = User::factory()->create([
            'role' => 'client',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '79991234567'
        ]);

        // Создаём активную смену
        $this->shift = Shift::create([
            'user_id' => $this->admin->id,
            'start_time' => now(),
            'status' => 'active'
        ]);

        // Создаём категорию
        $this->category = CategoryProduct::create([
            'name_category' => 'Пиццы',
            'description_category' => 'Вкусные пиццы'
        ]);

        // Создаём ингредиенты
        $this->ingredient1 = Ingredient::create([
            'name' => 'Тесто',
            'quantity' => 100.00,
            'unit' => 'кг'
        ]);

        $this->ingredient2 = Ingredient::create([
            'name' => 'Сыр',
            'quantity' => 50.00,
            'unit' => 'кг'
        ]);

        // Создаём продукт
        $this->product = Product::create([
            'name_product' => 'Маргарита',
            'description_product' => 'Классическая пицца',
            'price_product' => 450.00,
            'id_category' => $this->category->id,
            'img_product' => 'margherita.jpg'
        ]);
    }

    /**
     * Тест 1: Проверка оформления заказа авторизованным пользователем
     */
    public function test_авторизованный_пользователь_может_оформить_заказ(): void
    {
        $response = $this->actingAs($this->client)
            ->withSession([
                'cart' => [
                    $this->product->id => [
                        'price' => 450.00,
                        'quantity' => 2
                    ]
                ]
            ])
            ->post('/checkout', [
                'payment_method' => 'cash',
                'delivery_method' => 'pickup',
                'phone' => '79991234567',
                'notes' => 'Позвоните перед приездом'
            ]);

        // Проверяем что заказ был создан
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->client->id,
            'payment_method' => 'cash',
            'delivery_method' => 'pickup'
        ]);

        // Проверяем редирект на подтверждение
        $response->assertStatus(302);
        $response->assertRedirect('/checkout/confirmation/' . Order::latest()->first()->id);
    }

    /**
     * Тест 2: Проверка изменения статуса заказа администратором
     */
    public function test_администратор_может_изменить_статус_заказа(): void
    {
        // Создаём заказ
        $order = Order::create([
            'user_id' => $this->client->id,
            'shift_id' => $this->shift->id,
            'total_amount' => 450.00,
            'status' => 'В обработке',
            'payment_method' => 'cash',
            'delivery_method' => 'pickup',
            'phone' => '79991234567'
        ]);

        // Администратор меняет статус
        $response = $this->actingAs($this->admin)->patch(
            "/admin/orders/{$order->id}/status",
            ['status' => 'Готовится']
        );

        // Проверяем что статус изменился
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'Готовится'
        ]);
    }

    /**
     * Тест 3: Проверка добавления блюда администратором
     */
    public function test_админ_может_создать_блюдо (): void
    {
        Storage::fake('public');

        $productData = [
            'name_product' => 'Пепперони',
            'description_product' => 'Острая пицца с пепперони',
            'price_product' => 550.00,
            'id_category' => $this->category->id,
            'img_product' => UploadedFile::fake()->image('pepperoni.jpg'),
            'ingredients' => [
                [
                    'id' => $this->ingredient1->id,
                    'quantity' => 0.3
                ],
                [
                    'id' => $this->ingredient2->id,
                    'quantity' => 0.2
                ]
            ]
        ];

        // Отправляем POST запрос на создание продукта
        $response = $this->actingAs($this->admin)->post(
            '/admin/products',
            $productData
        );

        // Проверяем, что продукт был создан и существует в БД
        $this->assertDatabaseHas('products', [
            'name_product' => 'Пепперони',
            'price_product' => 550.00
        ]);

        // Проверяем редирект на список продуктов
        $response->assertRedirect('/admin/products');
    }
}
