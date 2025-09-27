<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
<<<<<<< HEAD
use App\Http\Controllers\DaDataController;
=======
use App\Http\Controllers\Api\AddressController;
>>>>>>> 0a2f531d9e689e1a73a43fa7763f980a2de195dc

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
<<<<<<< HEAD
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
=======
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/address/test', [AddressController::class, 'test'])->name('api.address.test');
Route::post('/address/suggest', [AddressController::class, 'suggest'])->name('api.address.suggest');
>>>>>>> 0a2f531d9e689e1a73a43fa7763f980a2de195dc
