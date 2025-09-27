<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DaDataController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/test', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API работает!',
        'timestamp' => now()
    ]);
});

Route::get('/simple-test', [DaDataController::class, 'simpleTest'])->name('api.simple-test');
Route::get('/dadata-test-config', [DaDataController::class, 'testConfig'])->name('api.dadata-test');
Route::get('/dadata-test-api', [DaDataController::class, 'testApi'])->name('api.dadata-test-api');
Route::get('/address-suggestions', [DaDataController::class, 'getAddressSuggestions'])->name('api.address-suggestions');
