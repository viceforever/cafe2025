<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    /**
     * Проверка метода isAdmin()
     */
    public function testIsAdminMethodWorks(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $client = User::factory()->create(['role' => 'client']);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($client->isAdmin());
    }

    /**
     * Проверка метода isManager()
     */
    public function testIsManagerMethodWorks(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $client = User::factory()->create(['role' => 'client']);

        $this->assertTrue($manager->isManager());
        $this->assertFalse($client->isManager());
    }

    /**
     * Проверка метода isClient()
     */
    public function testIsClientMethodWorks(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->assertTrue($client->isClient());
        $this->assertFalse($admin->isClient());
    }

    /**
     * Проверка метода hasRole()
     */
    public function testHasRoleMethodWorks(): void
    {
        $user = User::factory()->create(['role' => 'manager']);

        $this->assertTrue($user->hasRole('manager'));
        $this->assertFalse($user->hasRole('admin'));
    }

    /**
     * Проверка метода hasAnyRole()
     */
    public function testHasAnyRoleMethodWorks(): void
    {
        $user = User::factory()->create(['role' => 'manager']);

        $this->assertTrue($user->hasAnyRole(['admin', 'manager']));
        $this->assertFalse($user->hasAnyRole(['admin', 'client']));
    }

    /**
     * Проверка связи с заказами
     */
    public function testUserHasOrders(): void
    {
        $user = User::factory()->create();

        // Проверяем что у пользователя может быть много заказов
        $this->assertTrue(method_exists($user, 'orders'));
    }
}
