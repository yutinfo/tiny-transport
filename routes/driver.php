<?php
 
use Illuminate\Support\Facades\Route;
 
Route::get('/', [App\Http\Controllers\DriverTripController::class, 'index'])->name('dashboard');
Route::get('/profile', [App\Http\Controllers\DriverTripController::class, 'profile'])->name('profile');
// Keep /trips/history before /trips/{trip} so the wildcard does not capture it.
Route::get('/trips/history', [App\Http\Controllers\DriverTripController::class, 'history'])->name('trips.history');
Route::get('/trips/{trip}', [App\Http\Controllers\DriverTripController::class, 'showDriverTrip'])->name('trips.show');
Route::post('/trip-items/{tripItem}/delivery-status', [App\Http\Controllers\DriverTripController::class, 'updateDriverDeliveryStatus'])->name('trip-items.delivery-status');
Route::post('/trip-items/{tripItem}/payment-status', [App\Http\Controllers\DriverTripController::class, 'updateDriverPaymentStatus'])->name('trip-items.payment-status');
Route::post('/trips/{trip}/start', [App\Http\Controllers\DriverTripController::class, 'startTrip'])->name('trips.start');
Route::post('/trips/{trip}/submit', [App\Http\Controllers\DriverTripController::class, 'submitTrip'])->name('trips.submit');
