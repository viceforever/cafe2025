<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\CategoryProduct;
use App\Models\Ingredient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Негативные тесты - проверка обработки ошибочных ситуаций
 */
class NegativeTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $client;
    private $category;
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

        // Создаём обычного клиента
        $this->client = User::factory()->create([
            'role' => 'client',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '79991234567'
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
    }

    /**
     * Тест 1: Проверка что администратор не может создать блюдо, не указав название
     */
    public function test_админ_не_может_создать_блюдо_без_названия(): void
    {
        Storage::fake('public');

        $productData = [
            // Намеренно не указываем название
            'name_product' => '',
            'description_product' => 'Острая пицца',
            'price_product' => 550.00,
            'id_category' => $this->category->id,
            'img_product' => UploadedFile::fake()->image('product.jpg'),
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

        // Отправляем POST запрос на создание продукта без названия
        $response = $this->actingAs($this->admin)->post(
            '/admin/products',
            $productData
        );

        // Проверяем что получили ошибку валидации
        $response->assertSessionHasErrors('name_product');

        // Проверяем что продукт НЕ был создан в БД
        $this->assertDatabaseMissing('products', [
            'description_product' => 'Острая пицца',
            'price_product' => 550.00
        ]);
    }

    /**
     * Тест 2: Проверка, что обычный пользователь не может получить доступ к админ-панели
     */
    public function test_пользователь_не_имеет_доступа_к_админ_панели(): void
    {
        $response = $this->actingAs($this->client)->get('/admin/products');

        // Проверяем что доступ запрещён
        $response->assertStatus(403);
    }

    /**
     * Тест 3: Проверка, что неавторизованный пользователь не может оформить заказ
     */
    public function test_неавторизованный_пользователь_не_может_оформить_заказ(): void
    {
        $response = $this->get('/checkout');

        // Проверяем что неавторизованного пользователя редиректит на логин
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }
}
