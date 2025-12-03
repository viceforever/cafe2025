<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\CategoryProduct;
use App\Models\Shift;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $manager;
    private $client;
    private $order;
    private $shift;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->manager = User::factory()->manager()->create();
        $this->client = User::factory()->client()->create();

        $this->shift = Shift::create([
            'user_id' => $this->admin->id,
            'start_time' => now(),
            'status' => 'active'
        ]);

        // Создаём заказ
        $this->order = Order::create([
            'user_id' => $this->client->id,
            'shift_id' => $this->shift->id,
            'total_amount' => 450.00,
            'status' => 'В обработке',
            'payment_method' => 'cash',
            'delivery_method' => 'pickup',
            'phone' => '79991234567'
        ]);
    }

    /**
     * Проверка отображения списка заказов
     */
    public function testAdminCanViewOrdersList(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/orders');

        $response->assertStatus(200);
        $response->assertSee('Управление заказами');
    }

    /**
     * Проверка изменения статуса заказа администратором
     */
    public function testAdminCanUpdateOrderStatus(): void
    {
        $response = $this->actingAs($this->admin)->patch(
            "/admin/orders/{$this->order->id}/status",
            ['status' => 'Готовится']
        );

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'status' => 'Готовится'
        ]);
    }

    /**
     * Проверка изменения статуса заказа менеджером
     */
    public function testManagerCanUpdateOrderStatus(): void
    {
        $response = $this->actingAs($this->manager)->patch(
            "/manager/orders/{$this->order->id}/status",
            ['status' => 'Готов к выдаче']
        );

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'status' => 'Готов к выдаче'
        ]);
    }

    /**
     * Проверка отмены заказа
     */
    public function testAdminCanCancelOrder(): void
    {
        $response = $this->actingAs($this->admin)->patch(
            "/admin/orders/{$this->order->id}/cancel"
        );

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'status' => 'Отменен'
        ]);
    }

    /**
     * Проверка подтверждения заказа
     */
    public function testAdminCanConfirmOrder(): void
    {
        $response = $this->actingAs($this->admin)->patch(
            "/admin/orders/{$this->order->id}/confirm"
        );

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'status' => 'Подтвержден'
        ]);
    }

    /**
     * Проверка доступа пользователя к истории своих заказов
     */
    public function testClientCanAccessTheirOrders(): void
    {
        $response = $this->actingAs($this->client)->get('/profile');

        // В профиле должна быть информация о заказах
        $response->assertStatus(200);
    }

    /**
     * Проверка что клиент не может просматривать заказы других пользователей
     */
    public function testClientCannotViewOthersOrders(): void
    {
        $anotherClient = User::factory()->client()->create();
        
        $response = $this->actingAs($anotherClient)->get('/admin/orders');

        $response->assertStatus(403);
    }
}
