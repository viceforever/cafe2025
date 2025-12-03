<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Проверка регистрации нового пользователя
     */
    public function testUserCanRegister(): void
    {
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '79991234567',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->post('/register', $userData);

        // Проверяем что пользователь был создан
        $this->assertDatabaseHas('users', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '79991234567'
        ]);

        $response->assertStatus(302);
    }

    /**
     * Проверка регистрации с дубликатом телефона
     */
    public function testCannotRegisterWithDuplicatePhone(): void
    {
        $user = User::factory()->create([
            'phone' => '79991234567'
        ]);

        $userData = [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'phone' => '79991234567',  // Уже существует
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $response = $this->post('/register', $userData);

        // Проверяем что вернулась ошибка
        $response->assertStatus(302);
        $response->assertSessionHasErrors('phone');
    }

    /**
     * Проверка авторизации пользователя
     */
    public function testUserCanLogin(): void
    {
        $user = User::factory()->create([
            'phone' => '79991234567',
            'password' => bcrypt('password123')
        ]);

        $credentials = [
            'phone' => '79991234567',
            'password' => 'password123'
        ];

        $response = $this->post('/login', $credentials);

        // Проверяем что пользователь авторизован
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Проверка что неправильный пароль не даёт авторизоваться
     */
    public function testLoginFailsWithWrongPassword(): void
    {
        $user = User::factory()->create([
            'phone' => '79991234567',
            'password' => bcrypt('password123')
        ]);

        $credentials = [
            'phone' => '79991234567',
            'password' => 'wrongpassword'
        ];

        $response = $this->post('/login', $credentials);

        // Проверяем что пользователь не авторизован
        $this->assertGuest();
    }

    /**
     * Проверка редактирования профиля пользователя
     */
    public function testUserCanUpdateProfile(): void
    {
        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '79991234567'
        ]);

        $updatedData = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'phone' => '79991234567'
        ];

        $response = $this->actingAs($user)->post(
            '/profile',
            $updatedData
        );

        // Проверяем что профиль обновлён
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith'
        ]);
    }

    /**
     * Проверка изменения пароля
     */
    public function testUserCanChangePassword(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('oldpassword')
        ]);

        $response = $this->actingAs($user)->post(
            '/profile/change-password',
            [
                'current_password' => 'oldpassword',
                'new_password' => 'newpassword123',
                'new_password_confirmation' => 'newpassword123'
            ]
        );

        // Проверяем что пользователь всё ещё может авторизоваться с новым паролем
        $this->assertTrue(
            \Illuminate\Support\Facades\Hash::check('newpassword123', $user->fresh()->password)
        );
    }

    /**
     * Проверка выхода из аккаунта
     */
    public function testUserCanLogout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        // Проверяем что пользователь больше не авторизован
        $this->assertGuest();
    }

    /**
     * Проверка что администратор может создавать сотрудников
     */
    public function testAdminCanCreateEmployee(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $employeeData = [
            'first_name' => 'Иван',
            'last_name' => 'Сидоров',
            'phone' => '79991234567',
            'password' => 'password123',
            'role' => 'manager'
        ];

        $response = $this->actingAs($admin)->post(
            '/admin/users',
            $employeeData
        );

        $this->assertDatabaseHas('users', [
            'first_name' => 'Иван',
            'role' => 'manager'
        ]);
    }
}
