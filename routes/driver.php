<?php
 
use Illuminate\Support\Facades\Route;
 
Route::get('/', [App\Http\Controllers\DriverTripController::class, 'index'])->name('dashboard');
Route::get('/trips/{trip}', [App\Http\Controllers\DriverTripController::class, 'showDriverTrip'])->name('trips.show');
Route::post('/trip-items/{tripItem}/delivery-status', [App\Http\Controllers\DriverTripController::class, 'updateDriverDeliveryStatus'])->name('trip-items.delivery-status');
Route::post('/trip-items/{tripItem}/payment-status', [App\Http\Controllers\DriverTripController::class, 'updateDriverPaymentStatus'])->name('trip-items.payment-status');
