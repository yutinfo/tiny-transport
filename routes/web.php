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
    if (auth()->check() && auth()->user()->isDriver()) {
        return redirect()->route('driver.dashboard');
    }

    return redirect('admin');
});
Route::get('/web', fn () => view('web.tracking'))->name('web.tracking');

Route::get('/login', [App\Http\Controllers\LoginController::class, 'show'])->name('login.show');
Route::post('/login', [App\Http\Controllers\LoginController::class, 'login'])->name('login.login');
Route::post('/logout', [App\Http\Controllers\LogoutController::class, 'perform'])->name('login.logout');


