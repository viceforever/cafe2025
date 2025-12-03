<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\CategoryProduct;
use App\Models\Ingredient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $category;
    private $ingredient1;
    private $ingredient2;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Создаём администратора и категорию для всех тестов
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'first_name' => 'Admin',
            'last_name' => 'User'
        ]);

        $this->category = CategoryProduct::create([
            'name_category' => 'Пиццы',
            'description_category' => 'Вкусные пиццы'
        ]);

        // Создаем ингредиенты с достаточным количеством
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
     * Проверка добавления блюда администратором
     */
    public function testAdminCanCreateProduct(): void
    {
        Storage::fake('public');

        $productData = [
            'name_product' => 'Маргарита',
            'description_product' => 'Классическая пицца с помидорами',
            'price_product' => 450.00,
            'id_category' => $this->category->id,
            'img_product' => UploadedFile::fake()->image('margherita.jpg'),
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
            'name_product' => 'Маргарита',
            'price_product' => 450.00
        ]);

        // Проверяем редирект на список продуктов
        $response->assertRedirect('/admin/products');
    }

    /**
     * Проверка редактирования блюда администратором
     */
    public function testAdminCanEditProduct(): void
    {
        // Создаём продукт
        $product = Product::create([
            'name_product' => 'Маргарита',
            'description_product' => 'Классическая пицца',
            'price_product' => 450.00,
            'id_category' => $this->category->id,
            'img_product' => 'margherita.jpg'
        ]);

        // Добавляем ингредиенты к продукту
        $product->ingredients()->attach($this->ingredient1->id, ['quantity_needed' => 0.3]);
        $product->ingredients()->attach($this->ingredient2->id, ['quantity_needed' => 0.2]);

        Storage::fake('public');
        
        $updatedData = [
            'name_product' => 'Маргарита Premium',
            'description_product' => 'Маргарита с премиум ингредиентами',
            'price_product' => 550.00,
            'id_category' => $this->category->id,
            'img_product' => UploadedFile::fake()->image('margherita_premium.jpg'),
            'ingredients' => [
                [
                    'id' => $this->ingredient1->id,
                    'quantity' => 0.4
                ],
                [
                    'id' => $this->ingredient2->id,
                    'quantity' => 0.3
                ]
            ]
        ];

        $response = $this->actingAs($this->admin)->put(
            "/admin/products/{$product->id}",
            $updatedData
        );

        // Проверяем, что данные были обновлены
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name_product' => 'Маргарита Premium',
            'price_product' => 550.00
        ]);
    }

    /**
     * Проверка удаления блюда администратором
     */
    public function testAdminCanDeleteProduct(): void
    {
        // Создаём продукт
        $product = Product::create([
            'name_product' => 'Маргарита',
            'description_product' => 'Классическая пицца',
            'price_product' => 450.00,
            'id_category' => $this->category->id,
            'img_product' => 'margherita.jpg'
        ]);

        $productId = $product->id;

        // Удаляем продукт
        $response = $this->actingAs($this->admin)->delete(
            "/admin/products/{$productId}"
        );

        // Проверяем, что продукт был удален из БД
        $this->assertDatabaseMissing('products', [
            'id' => $productId
        ]);
    }

    /**
     * Проверка создания категории администратором
     */
    public function testAdminCanCreateCategory(): void
    {
        $categoryData = [
            'name_category' => 'Десерты',
            'description_category' => 'Сладкие десерты'
        ];

        $response = $this->actingAs($this->admin)->post(
            '/admin/categories',
            $categoryData
        );

        // Проверяем, что категория создана
        $this->assertDatabaseHas('category_products', [
            'name_category' => 'Десерты'
        ]);

        $response->assertRedirect('/admin/categories');
    }

    /**
     * Проверка что клиент не может создавать продукты
     */
    public function testClientCannotCreateProduct(): void
    {
        $client = User::factory()->create([
            'role' => 'client'
        ]);

        $productData = [
            'name_product' => 'Маргарита',
            'price_product' => 450.00,
            'id_category' => $this->category->id
        ];

        $response = $this->actingAs($client)->post(
            '/admin/products',
            $productData
        );

        $response->assertStatus(403);
    }
}
