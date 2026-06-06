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
        Route::get('/{order}/labels', [App\Http\Controllers\ParcelLabelController::class, 'order'])->name('admin.orders.labels');
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
    Route::prefix('trips')->group(function () {
        Route::get('/', [App\Http\Controllers\TripController::class, 'index'])->name('admin.trips.index');
        Route::get('/export/csv', [App\Http\Controllers\TripExportController::class, 'tripsCsv'])->name('admin.trips.export.csv');
        Route::get('/create', [App\Http\Controllers\TripController::class, 'create'])->name('admin.trips.create');
        Route::post('/', [App\Http\Controllers\TripController::class, 'store'])->name('admin.trips.store');
        Route::get('/{trip}/assign', [App\Http\Controllers\TripController::class, 'assign'])->name('admin.trips.assign');
        Route::post('/{trip}/assign-items', [App\Http\Controllers\TripController::class, 'assignItems'])->name('admin.trips.assign-items');
        Route::post('/{trip}/assign-status', [App\Http\Controllers\TripController::class, 'assignStatus'])->name('admin.trips.assign-status');
        Route::post('/{trip}/start', [App\Http\Controllers\TripController::class, 'start'])->name('admin.trips.start');
        Route::post('/{trip}/cancel', [App\Http\Controllers\TripController::class, 'cancel'])->name('admin.trips.cancel');
        Route::post('/{trip}/complete', [App\Http\Controllers\TripController::class, 'complete'])->name('admin.trips.complete');
        Route::get('/{trip}/driver', [App\Http\Controllers\DriverTripController::class, 'show'])->name('admin.trips.driver');
        Route::get('/{trip}/labels', [App\Http\Controllers\ParcelLabelController::class, 'trip'])->name('admin.trips.labels');
        Route::post('/{trip}/costs', [App\Http\Controllers\TripCostController::class, 'store'])->name('admin.trips.costs.store');
        Route::get('/{trip}/items/export/csv', [App\Http\Controllers\TripExportController::class, 'tripItemsCsv'])->name('admin.trips.items.export.csv');
        Route::get('/{trip}/cod/export/csv', [App\Http\Controllers\TripExportController::class, 'tripCodCsv'])->name('admin.trips.cod.export.csv');
        Route::get('/{trip}', [App\Http\Controllers\TripController::class, 'show'])->name('admin.trips.show');
        Route::get('/{trip}/edit', [App\Http\Controllers\TripController::class, 'edit'])->name('admin.trips.edit');
        Route::match(['put', 'patch', 'post'], '/{trip}', [App\Http\Controllers\TripController::class, 'update'])->name('admin.trips.update');
    });
    Route::post('/trip-items/{tripItem}/remove', [App\Http\Controllers\TripController::class, 'removeItem'])->name('admin.trip-items.remove');
    Route::post('/trip-items/{tripItem}/delivery-status', [App\Http\Controllers\TripController::class, 'updateDeliveryStatus'])->name('admin.trip-items.delivery-status');
    Route::post('/trip-items/{tripItem}/payment-status', [App\Http\Controllers\TripController::class, 'updatePaymentStatus'])->name('admin.trip-items.payment-status');
    Route::delete('/trip-costs/{tripCost}', [App\Http\Controllers\TripCostController::class, 'destroy'])->name('admin.trip-costs.destroy');
    Route::post('/driver/trip-items/{tripItem}/delivery-status', [App\Http\Controllers\DriverTripController::class, 'updateDeliveryStatus'])->name('admin.driver.trip-items.delivery-status');
    Route::post('/driver/trip-items/{tripItem}/payment-status', [App\Http\Controllers\DriverTripController::class, 'updatePaymentStatus'])->name('admin.driver.trip-items.payment-status');
    Route::get('/parcels/search', [App\Http\Controllers\ParcelTrackingController::class, 'search'])->name('admin.parcels.search');
    Route::get('/parcels/code/{parcelCode}', [App\Http\Controllers\ParcelTrackingController::class, 'code'])->name('admin.parcels.code');
    Route::get('/parcels/{orderReceive}/tracking', [App\Http\Controllers\ParcelTrackingController::class, 'show'])->name('admin.parcels.tracking');
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
