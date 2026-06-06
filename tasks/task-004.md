# Task 004: Driver Routes, Redirects, and Access Control

## Goal

สร้างพื้นที่ `/driver` สำหรับคนขับที่ล็อกอินแล้ว และป้องกันไม่ให้ role `driver` เข้าเมนูงาน admin/staff

## Decision

เพิ่ม middleware แบบรับ role list เพื่อใช้กับทั้ง admin/staff และ driver routes แทนการเพิ่ม middleware เฉพาะกิจหลายตัว

## Files

- Create: `app/Http/Middleware/EnsureRole.php`
- Modify: `app/Http/Kernel.php`
- Modify: `routes/web.php`
- Modify: `routes/admin.php`
- Create: `routes/driver.php`
- Modify: `app/Providers/RouteServiceProvider.php`
- Modify: `app/Http/Controllers/LoginController.php`
- Modify: `app/Http/Controllers/DriverTripController.php`
- Create: `resources/views/driver/trips/index.blade.php`
- Test: `tests/Feature/DriverPortalAccessFeatureTest.php`

## Steps

- [ ] Create `app/Http/Middleware/EnsureRole.php`.

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role_name, $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}
```

- [ ] Register middleware in `app/Http/Kernel.php`.

```php
'role' => \App\Http\Middleware\EnsureRole::class,
```

- [ ] Add `routes/driver.php`.

```php
<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [App\Http\Controllers\DriverTripController::class, 'index'])->name('dashboard');
Route::get('/trips/{trip}', [App\Http\Controllers\DriverTripController::class, 'showDriverTrip'])->name('trips.show');
Route::post('/trip-items/{tripItem}/delivery-status', [App\Http\Controllers\DriverTripController::class, 'updateDriverDeliveryStatus'])->name('trip-items.delivery-status');
Route::post('/trip-items/{tripItem}/payment-status', [App\Http\Controllers\DriverTripController::class, 'updateDriverPaymentStatus'])->name('trip-items.payment-status');
```

- [ ] Register `routes/driver.php` in `RouteServiceProvider::boot()` with prefix `/driver`, middleware `web`, `auth`, and `role:driver`.

```php
Route::middleware(['web', 'auth', 'role:driver'])
    ->prefix('driver')
    ->as('driver.')
    ->group(base_path('routes/driver.php'));
```

- [ ] Update `/` in `routes/web.php` to redirect authenticated drivers to `/driver`.

```php
Route::get('/', function () {
    if (auth()->check() && auth()->user()->isDriver()) {
        return redirect()->route('driver.dashboard');
    }

    return redirect('admin');
});
```

- [ ] Update `LoginController::authenticated()` so each role lands in the correct area.

```php
protected function authenticated(Request $request, $user)
{
    if ($user->isAdmin()) {
        return redirect()->intended(route('admin.dashboard'));
    }

    if ($user->isDriver()) {
        return redirect()->intended(route('driver.dashboard'));
    }

    return redirect()->intended(route('admin.orders.create'));
}
```

- [ ] Protect admin/staff work routes in `routes/admin.php`.

```php
Route::middleware(['auth'])->group(function () {
    Route::middleware('role:admin,staff')->group(function () {
        Route::prefix('orders')->group(function () {
            // keep the existing order routes here
        });

        Route::prefix('contacts')->group(function () {
            // keep the existing contact routes here
        });

        Route::prefix('api')->group(function () {
            // keep the existing admin contact API routes here
        });

        Route::prefix('trips')->group(function () {
            // keep the existing trip routes here
        });

        // keep existing order receive, trip item, driver preview, parcel search, and notification routes here
    });

    Route::prefix('dashboard')->middleware('role:admin')->group(function () {
        // keep the existing dashboard routes here
    });

    Route::prefix('users')->middleware('role:admin')->group(function () {
        // keep the existing user management routes here
    });
});
```

- [ ] Add `DriverTripController::index()` for the driver dashboard list.

```php
public function index()
{
    $trips = Trip::query()
        ->where('driver_user_id', Auth::id())
        ->whereNotIn('status', [Trip::STATUS_COMPLETED, Trip::STATUS_CANCELLED])
        ->orderByDesc('trip_date')
        ->orderByDesc('id')
        ->withCount('tripItems')
        ->paginate(10);

    return view('driver.trips.index', [
        'trips' => $trips,
    ]);
}
```

- [ ] Create a minimal `resources/views/driver/trips/index.blade.php` so `/driver` works before the mobile layout task.

```blade
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <section class="content-header">
        <div class="card">
            <div class="card-header">
                <h5 class="font-weight-bold mb-0">งานขนส่งของฉัน</h5>
            </div>
        </div>
    </section>
    <section class="content">
        @forelse($trips as $trip)
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>{{ $trip->code }}</strong>
                            <div class="text-muted small">{{ optional($trip->trip_date)->format('Y-m-d') }}</div>
                        </div>
                        <a href="{{ route('driver.trips.show', $trip) }}" class="btn btn-sm bg-primary">เปิด</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center text-muted">ยังไม่มีรอบขนส่งที่ได้รับมอบหมาย</div>
            </div>
        @endforelse
    </section>
</div>
@endsection
```

- [ ] Add `showDriverTrip(Trip $trip)` and an ownership guard in `DriverTripController`.

```php
public function showDriverTrip(Trip $trip)
{
    $this->ensureDriverOwnsTrip($trip);

    return $this->show($trip);
}

private function ensureDriverOwnsTrip(Trip $trip): void
{
    if ((int) $trip->driver_user_id !== (int) Auth::id()) {
        abort(403);
    }
}
```

- [ ] Add `tests/Feature/DriverPortalAccessFeatureTest.php`.

```php
<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverPortalAccessFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_driver_login_redirects_to_driver_dashboard()
    {
        User::create([
            'username' => 'driver-login',
            'password' => 'password',
            'name' => 'Driver',
            'last_name' => 'Login',
            'email' => 'driver-login@example.com',
            'status' => 'active',
            'role_name' => User::ROLE_DRIVER,
        ]);

        $this->post('/login', [
            'username' => 'driver-login',
            'password' => 'password',
        ])->assertRedirect('/driver');
    }

    public function test_driver_cannot_open_admin_orders()
    {
        $driver = User::create([
            'username' => 'driver-no-admin',
            'password' => 'password',
            'name' => 'Driver',
            'last_name' => 'NoAdmin',
            'email' => 'driver-no-admin@example.com',
            'status' => 'active',
            'role_name' => User::ROLE_DRIVER,
        ]);

        $this->actingAs($driver)
            ->get('/admin/orders')
            ->assertForbidden();
    }

    public function test_driver_can_only_open_assigned_trip()
    {
        $driver = User::create([
            'username' => 'driver-own-trip',
            'password' => 'password',
            'name' => 'Driver',
            'last_name' => 'OwnTrip',
            'email' => 'driver-own-trip@example.com',
            'status' => 'active',
            'role_name' => User::ROLE_DRIVER,
        ]);

        $ownTrip = Trip::create([
            'code' => 'RUN-20260607-0001',
            'trip_date' => '2026-06-07',
            'driver_user_id' => $driver->id,
            'status' => Trip::STATUS_IN_TRANSIT,
        ]);

        $otherTrip = Trip::create([
            'code' => 'RUN-20260607-0002',
            'trip_date' => '2026-06-07',
            'status' => Trip::STATUS_IN_TRANSIT,
        ]);

        $this->actingAs($driver)
            ->get('/driver/trips/' . $ownTrip->id)
            ->assertOk();

        $this->actingAs($driver)
            ->get('/driver/trips/' . $otherTrip->id)
            ->assertForbidden();
    }
}
```

## Validation

Run inside Docker:

```bash
docker compose exec app php artisan test --filter=DriverPortalAccessFeatureTest
```

Expected result: driver redirect, admin blocking, and trip ownership tests pass.

## Commit

```bash
git add app/Http/Middleware/EnsureRole.php app/Http/Kernel.php routes/web.php routes/admin.php routes/driver.php app/Providers/RouteServiceProvider.php app/Http/Controllers/LoginController.php app/Http/Controllers/DriverTripController.php resources/views/driver/trips/index.blade.php tests/Feature/DriverPortalAccessFeatureTest.php
git commit -m "feat: add protected driver portal routes"
```

## Acceptance Criteria

- Driver login goes to `/driver`.
- Driver users cannot open admin/staff routes.
- Admin/staff routes still work for allowed roles.
- Drivers can open only trips assigned to their `users.id`.
