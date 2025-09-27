<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\DaDataController;

use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\IngredientController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Manager\ManagerController;
use App\Http\Controllers\Manager\ShiftController;
use App\Http\Controllers\Employee\ScheduleController as EmployeeScheduleController;

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register')->middleware('guest');
Route::post('/register', [RegisterController::class, 'register'])->middleware('guest');

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/', [ProductController::class, 'index'])->name('home');
Route::get('/product/{id}', [ProductController::class, 'show'])->name('product.show');

// Поиск товара
Route::get('/search', [ProductController::class, 'search'])->name('products.search');

Route::get('/test-dadata', function () {
    return view('test-dadata');
})->name('test.dadata');

// Маршруты для добавления/удаления/обновления корзины
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add/{id}', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/update/{id}', [CartController::class, 'update'])->name('cart.update');

// Оформление заказа
Route::middleware(['auth'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::get('/checkout/confirmation/{order}', [CheckoutController::class, 'confirmation'])->name('checkout.confirmation');
});

// Профиль пользователя
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::get('/help', [MainController::class, 'index'])->name('help');

Route::middleware(['auth', App\Http\Middleware\CheckRole::class.':manager,admin'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', [ManagerController::class, 'dashboard'])->name('dashboard');
    Route::get('/orders', [ManagerController::class, 'orders'])->name('orders');
    Route::patch('/orders/{order}/status', [ManagerController::class, 'updateOrderStatus'])->name('orders.update-status');
    Route::patch('/orders/{order}/confirm', [ManagerController::class, 'confirmOrder'])->name('orders.confirm');
    Route::patch('/orders/{order}/cancel', [ManagerController::class, 'cancelOrder'])->name('orders.cancel');
    Route::get('/ingredients', [ManagerController::class, 'ingredients'])->name('ingredients');
    Route::get('/products/availability', [ManagerController::class, 'checkProductAvailability'])->name('products.availability');
    
    // Управление сменами
    Route::post('/shift/start', [ManagerController::class, 'startShift'])->name('shift.start');
    Route::post('/shift/end', [ManagerController::class, 'endShift'])->name('shift.end');
    Route::get('/shift/stats', [ManagerController::class, 'getShiftStats'])->name('shift.stats');
    Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.index');
    Route::get('/shifts/{shift}', [ShiftController::class, 'show'])->name('shifts.show');
});

Route::middleware(['auth', App\Http\Middleware\CheckRole::class.':manager,admin'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/schedule', [EmployeeScheduleController::class, 'index'])->name('schedule.index');
    Route::get('/schedule/upcoming', [EmployeeScheduleController::class, 'upcoming'])->name('schedule.upcoming');
});

Route::middleware(['auth', App\Http\Middleware\CheckRole::class.':admin'])->prefix('admin')->name('admin.')->group(function () {
    // Управление товарами
    Route::get('/products', [AdminController::class, 'index'])->name('products.index');
    Route::get('/products/create', [AdminController::class, 'create'])->name('products.create');
    Route::post('/products', [AdminController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [AdminController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [AdminController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [AdminController::class, 'destroy'])->name('products.destroy');

    // Управление категориями
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Управление заказами
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::patch('/orders/{order}/confirm', [AdminOrderController::class, 'confirm'])->name('orders.confirm');
    Route::patch('/orders/{order}/cancel', [AdminOrderController::class, 'cancel'])->name('orders.cancel');
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');

    Route::resource('users', EmployeeController::class);
    Route::patch('/users/{user}/role', [EmployeeController::class, 'updateRole'])->name('users.update-role');

    Route::resource('ingredients', IngredientController::class);
    Route::patch('/ingredients/{ingredient}/quantity', [IngredientController::class, 'updateQuantity'])->name('ingredients.update-quantity');
    Route::get('/ingredients-all', [IngredientController::class, 'getAll'])->name('ingredients.get-all');

    Route::resource('schedules', ScheduleController::class);
    Route::get('/schedules-list', [ScheduleController::class, 'list'])->name('schedules.list');
    Route::get('/schedules-bulk/create', [ScheduleController::class, 'bulkCreate'])->name('schedules.bulk-create');
    Route::post('/schedules-bulk', [ScheduleController::class, 'bulkStore'])->name('schedules.bulk-store');

    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/products', [AnalyticsController::class, 'products'])->name('analytics.products');
    Route::get('/analytics/ingredients', [AnalyticsController::class, 'ingredients'])->name('analytics.ingredients');
    Route::get('/analytics/financial', [AnalyticsController::class, 'financial'])->name('analytics.financial');

    Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
    Route::get('/reports/sales', [ReportsController::class, 'salesReport'])->name('reports.sales');
    Route::get('/reports/ingredients', [ReportsController::class, 'ingredientsReport'])->name('reports.ingredients');
    Route::get('/reports/sales/export/{format}', [ReportsController::class, 'exportSalesReport'])->name('reports.sales.export');
    Route::get('/reports/ingredients/export/{format}', [ReportsController::class, 'exportIngredientsReport'])->name('reports.ingredients.export');

    // Маршрут для загружаемой картинки
    Route::get('/images/{filename}', function ($filename) {
        return response()->file(public_path('images/' . $filename));
    });
});
