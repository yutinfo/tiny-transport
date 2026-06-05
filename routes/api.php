<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
// Route::middleware(['auth'])->group(function () {

    Route::get('/province', [App\Http\Controllers\Api\ProvinceController::class, 'index'])->name('api.province.index');
    Route::get('/province/{id}', [App\Http\Controllers\Api\ProvinceController::class, 'show'])->name('api.province.show');

    Route::get('/amphure', [App\Http\Controllers\Api\AmphureController::class, 'index'])->name('api.amphure.index');
    Route::get('/amphure/{id}', [App\Http\Controllers\Api\AmphureController::class, 'show'])->name('api.amphure.show');

    Route::get('/district', [App\Http\Controllers\Api\DistrictController::class, 'index'])->name('api.district.index');
    Route::get('/district/{id}', [App\Http\Controllers\Api\DistrictController::class, 'show'])->name('api.district.show');

    Route::get('/order', [App\Http\Controllers\Api\OrderController::class, 'index'])->name('api.order.index');
    Route::get('/contacts/suggest', [App\Http\Controllers\Api\ContactController::class, 'suggest'])->name('api.contacts.suggest');
    Route::get('/contacts/search', [App\Http\Controllers\Api\ContactController::class, 'search'])->name('api.contacts.search');
// });
