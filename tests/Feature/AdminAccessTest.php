<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Проверка, что администратор может получить доступ к админ-панели
     */
    public function testAdminCanAccessAdminPanel(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'first_name' => 'Admin',
            'last_name' => 'User'
        ]);

        $response = $this->actingAs($admin)->get('/admin/products');

        $response->assertStatus(200);
        $response->assertSee('Управление товарами');
    }

    /**
     * Проверка, что обычный пользователь не может получить доступ к админ-панели
     */
    public function testClientCannotAccessAdminPanel(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);

        $response = $this->actingAs($client)->get('/admin/products');

        $response->assertStatus(403);
    }

    /**
     * Проверка, что неавторизованного пользователя редиректит на логин
     */
    public function testUnauthenticatedUserIsRedirectedToLogin(): void
    {
        $response = $this->get('/admin/products');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * Проверка что менеджер НЕ может получить доступ к админ-панели продуктов
     * (менеджер может получить доступ только к маршруту /manager/*, не /admin/*)
     */
    public function testManagerCannotAccessAdminPanel(): void
    {
        $manager = User::factory()->create([
            'role' => 'manager',
            'first_name' => 'Manager',
            'last_name' => 'User'
        ]);

        $response = $this->actingAs($manager)->get('/admin/products');

        // Manager должен получить 403 для /admin/* маршрутов
        $response->assertStatus(403);
    }

    /**
     * Проверка что менеджер может получить доступ к менеджер-панели
     */
    public function testManagerCanAccessManagerPanel(): void
    {
        $manager = User::factory()->create([
            'role' => 'manager',
            'first_name' => 'Manager',
            'last_name' => 'User'
        ]);

        $response = $this->actingAs($manager)->get('/manager/dashboard');

        $response->assertStatus(200);
    }
}
