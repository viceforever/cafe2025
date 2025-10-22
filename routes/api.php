<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AddressController;

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

Route::get('/address-suggestions', [AddressController::class, 'getSuggestions'])->name('api.address.suggestions');
Route::post('/address/suggest', [AddressController::class, 'suggest'])->name('api.address.suggest');
Route::get('/address/test', [AddressController::class, 'test'])->name('api.address.test');
