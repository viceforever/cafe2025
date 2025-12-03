<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\CategoryProduct;
use App\Models\Order;
use App\Models\Shift;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $product;
    private $shift;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => 'client',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '79991234567'
        ]);

        $this->shift = Shift::create([
            'user_id' => User::factory()->admin()->create()->id,
            'start_time' => now(),
            'status' => 'active'
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
     * Проверка оформления заказа авторизованным пользователем
     */
    public function testAuthorizedUserCanCheckout(): void
    {
        $response = $this->actingAs($this->user)
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
            'user_id' => $this->user->id,
            'payment_method' => 'cash',
            'delivery_method' => 'pickup'
        ]);

        // Проверяем редирект на подтверждение
        $response->assertStatus(302);
        $response->assertRedirect('/checkout/confirmation/' . Order::latest()->first()->id);
    }

    /**
     * Проверка редиректа неавторизованного пользователя при попытке оформления заказа
     */
    public function testUnauthenticatedUserIsRedirectedFromCheckout(): void
    {
        $response = $this->get('/checkout');

        // Должен редиректить на логин
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * Проверка выбора способа оплаты
     */
    public function testCheckoutWithCardPayment(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession([
                'cart' => [
                    $this->product->id => [
                        'price' => 450.00,
                        'quantity' => 1
                    ]
                ]
            ])
            ->post('/checkout', [
                'payment_method' => 'card',
                'delivery_method' => 'delivery',
                'delivery_city' => 'Иркутск',
                'delivery_street' => 'ул. Ленина',
                'delivery_house' => '10',
                'phone' => '79991234567'
            ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'payment_method' => 'card'
        ]);
    }

    /**
     * Проверка выбора способа доставки
     */
    public function testCheckoutWithDelivery(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession([
                'cart' => [
                    $this->product->id => [
                        'price' => 450.00,
                        'quantity' => 1
                    ]
                ]
            ])
            ->post('/checkout', [
                'payment_method' => 'cash',
                'delivery_method' => 'delivery',
                'delivery_city' => 'Иркутск',
                'delivery_street' => 'ул. Советская',
                'delivery_house' => '5',
                'phone' => '79991234567'
            ]);

        $this->assertDatabaseHas('orders', [
            'delivery_method' => 'delivery'
        ]);
    }

    /**
     * Проверка подтверждения заказа
     */
    public function testUserCanViewOrderConfirmation(): void
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'shift_id' => $this->shift->id,
            'total_amount' => 450.00,
            'status' => 'В обработке',
            'payment_method' => 'cash',
            'delivery_method' => 'pickup',
            'phone' => '79991234567'
        ]);

        $response = $this->actingAs($this->user)->get(
            "/checkout/confirmation/{$order->id}"
        );

        // Проверяем что страница доступна
        $response->assertStatus(200);
    }
}
