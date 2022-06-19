<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('/ta-admin/dashboard');
});
Route::middleware(['auth'])->group(function () {

    Route::prefix('orders')->group(function () {
        Route::get('/', [App\Http\Controllers\OrderController::class, 'index'])->name('ta-admin.orders.index');
        Route::get('/create', [App\Http\Controllers\OrderController::class, 'create'])->name('ta-admin.orders.create');
        Route::get('/{id}', [App\Http\Controllers\OrderController::class, 'edit'])->name('ta-admin.orders.edit');
        Route::post('/store', [App\Http\Controllers\OrderController::class, 'store'])->name('ta-admin.orders.store');
        Route::put('/update/{id}', [App\Http\Controllers\OrderController::class, 'update'])->name('ta-admin.orders.update');

    });
    Route::prefix('dashboard')->group(function () {

        Route::get('/', [App\Http\Controllers\DashboardController::class, 'index'])->name('ta-admin.dashboard');
        Route::get('/create', [App\Http\Controllers\ReportController::class, 'create'])->name('ta-admin.reports.create');
        Route::get('/update', [App\Http\Controllers\ReportController::class, 'update'])->name('ta-admin.reports.update');
    });
    Route::prefix('users')->group(function () {

        Route::get('/', [App\Http\Controllers\UserController::class, 'index'])->name('ta-admin.users.index');
        Route::get('/create', [App\Http\Controllers\UserController::class, 'create'])->name('ta-admin.users.create');
        Route::post('/store', [App\Http\Controllers\UserController::class, 'store'])->name('ta-admin.users.store');
        Route::get('/{id}', [App\Http\Controllers\UserController::class, 'edit'])->name('ta-admin.users.edit');
        Route::put('/update/{id}', [App\Http\Controllers\UserController::class, 'update'])->name('ta-admin.users.update');
    });
});



