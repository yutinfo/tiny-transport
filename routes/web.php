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
    if (auth()->check()) {
        return auth()->user()->isDriver()
            ? redirect()->route('driver.dashboard')
            : redirect('admin');
    }

    return view('landing.index');
})->name('landing');
Route::get('/tracking', fn () => view('tracking.index'))->name('tracking.show');

// Legacy path: permanent redirect to /tracking, preserving the query string so
// old shared links / printed QR codes (e.g. /web?q=CODE) keep working.
Route::get('/web', function (Illuminate\Http\Request $request) {
    $query = $request->getQueryString();

    return redirect('/tracking' . ($query ? '?' . $query : ''), 301);
});

Route::get('/login', [App\Http\Controllers\LoginController::class, 'show'])->name('login.show');
Route::post('/login', [App\Http\Controllers\LoginController::class, 'login'])->name('login.login');
Route::post('/logout', [App\Http\Controllers\LogoutController::class, 'perform'])->name('login.logout');


