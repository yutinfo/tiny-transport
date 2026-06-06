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
    if (auth()->check() && auth()->user()->role_name !== 'admin') {
        return redirect('/admin/orders/create');
    }

    return redirect('/admin/dashboard');
});
Route::middleware(['auth'])->group(function () {

    Route::prefix('orders')->group(function () {
        Route::get('/', [App\Http\Controllers\OrderController::class, 'index'])->name('admin.orders.index');
        Route::get('/create', [App\Http\Controllers\OrderController::class, 'create'])->name('admin.orders.create');
        Route::get('/{id}', [App\Http\Controllers\OrderController::class, 'edit'])->name('admin.orders.edit');
        Route::post('/store', [App\Http\Controllers\OrderController::class, 'store'])->name('admin.orders.store');
        Route::put('/update/{id}', [App\Http\Controllers\OrderController::class, 'update'])->name('admin.orders.update');

    });
    Route::delete('/order-receive/{id}', [App\Http\Controllers\Api\OrderReciverController::class, 'destroy'])->name('admin.orderreceive.delete');
    Route::prefix('contacts')->group(function () {
        Route::get('/', [App\Http\Controllers\ContactController::class, 'index'])->name('admin.contacts.index');
        Route::get('/create', [App\Http\Controllers\ContactController::class, 'create'])->name('admin.contacts.create');
        Route::post('/store', [App\Http\Controllers\ContactController::class, 'store'])->name('admin.contacts.store');
        Route::get('/{id}', [App\Http\Controllers\ContactController::class, 'edit'])->name('admin.contacts.edit');
        Route::put('/update/{id}', [App\Http\Controllers\ContactController::class, 'update'])->name('admin.contacts.update');
        Route::delete('/delete/{id}', [App\Http\Controllers\ContactController::class, 'destroy'])->name('admin.contacts.destroy');
    });
    Route::prefix('api')->group(function () {
        Route::get('/contacts/suggest', [App\Http\Controllers\Api\ContactController::class, 'suggest'])->name('admin.api.contacts.suggest');
        Route::get('/contacts/search', [App\Http\Controllers\Api\ContactController::class, 'search'])->name('admin.api.contacts.search');
    });
    Route::prefix('dashboard')->middleware('admin')->group(function () {

        Route::get('/', [App\Http\Controllers\DashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/create', [App\Http\Controllers\ReportController::class, 'create'])->name('admin.reports.create');
        Route::get('/update', [App\Http\Controllers\ReportController::class, 'update'])->name('admin.reports.update');
    });
    Route::prefix('users')->middleware('admin')->group(function () {

        Route::get('/', [App\Http\Controllers\UserController::class, 'index'])->name('admin.users.index');
        Route::get('/create', [App\Http\Controllers\UserController::class, 'create'])->name('admin.users.create');
        Route::post('/store', [App\Http\Controllers\UserController::class, 'store'])->name('admin.users.store');
        Route::get('/{id}', [App\Http\Controllers\UserController::class, 'edit'])->name('admin.users.edit');
        Route::put('/update/{id}', [App\Http\Controllers\UserController::class, 'update'])->name('admin.users.update');
    });
});



