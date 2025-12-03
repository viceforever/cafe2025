<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderModelTest extends TestCase
{
    use RefreshDatabase;

    private $order;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->order = Order::create([
            'user_id' => $this->user->id,
            'total_amount' => 450.00,
            'status' => 'В обработке',
            'payment_method' => 'cash',
            'delivery_method' => 'pickup',
            'delivery_address' => 'ул. Ленина, 10',
            'phone' => '79991234567'
        ]);
    }

    /**
     * Проверка что заказ принадлежит пользователю
     */
    public function testOrderBelongsToUser(): void
    {
        $this->assertEquals($this->order->user->id, $this->user->id);
    }

    /**
     * Проверка метода getPaymentMethodTextAttribute
     */
    public function testPaymentMethodTextAttribute(): void
    {
        $this->assertEquals($this->order->payment_method_text, 'Наличными');

        $order2 = Order::create([
            'user_id' => $this->user->id,
            'total_amount' => 500.00,
            'status' => 'В обработке',
            'payment_method' => 'card',
            'delivery_method' => 'pickup',
            'phone' => '79991234567'
        ]);

        $this->assertEquals($order2->payment_method_text, 'Картой');
    }

    /**
     * Проверка метода getDeliveryMethodTextAttribute
     */
    public function testDeliveryMethodTextAttribute(): void
    {
        $this->assertEquals($this->order->delivery_method_text, 'Самовывоз');

        $order2 = Order::create([
            'user_id' => $this->user->id,
            'total_amount' => 500.00,
            'status' => 'В обработке',
            'payment_method' => 'cash',
            'delivery_method' => 'delivery',
            'delivery_address' => 'ул. Ленина, 20',
            'phone' => '79991234567'
        ]);

        $this->assertEquals($order2->delivery_method_text, 'Доставка');
    }

    /**
     * Проверка что сумма заказа корректна
     */
    public function testOrderTotalAmountIsCorrect(): void
    {
        $this->assertEquals($this->order->total_amount, 450.00);
    }

    /**
     * Проверка начального статуса заказа
     */
    public function testOrderInitialStatus(): void
    {
        $this->assertEquals($this->order->status, 'В обработке');
    }
}
