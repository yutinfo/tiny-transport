# Task 005: Mobile-First Driver Layout

## Goal

ทำหน้าคนขับให้เป็น mobile แนวตั้ง 100% โดยแยกจาก AdminLTE sidebar layout และยังใช้ Bootstrap/AdminLTE utility เดิมเท่าที่จำเป็น

## Decision

สร้าง layout ใหม่ `layouts.driver` เพื่อให้หน้าคนขับไม่มี sidebar, ไม่มี desktop admin wrapper, และมี viewport แนวตั้งเต็มจอสำหรับมือถือ

## Files

- Create: `resources/views/layouts/driver.blade.php`
- Modify: `resources/views/driver/trips/index.blade.php`
- Create: `resources/views/driver/trips/show.blade.php`
- Create: `resources/views/driver/trips/_parcel-card.blade.php`
- Create: `resources/sass/_driver.scss`
- Modify: `resources/sass/app.scss`
- Modify: `app/Http/Controllers/DriverTripController.php`
- Test: `tests/Feature/DriverMobileViewFeatureTest.php`

## Steps

- [ ] Create `resources/views/layouts/driver.blade.php`.

```blade
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>{{ config('app.name') }}</title>
    <meta content="width=device-width, initial-scale=1, viewport-fit=cover" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="/plugins/fontawesome-free/css/all.min.css">
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    @stack('page_css')
</head>
<body class="driver-shell">
    <main class="driver-app">
        @yield('content')
    </main>
    <script src="{{ mix('js/app.js') }}"></script>
    @stack('page_scripts')
</body>
</html>
```

- [ ] Create `resources/sass/_driver.scss`.

```scss
.driver-shell {
    min-height: 100vh;
    margin: 0;
    background: #f4f6f8;
    color: #1f2933;
    overflow-x: hidden;
}

.driver-app {
    width: 100%;
    max-width: 480px;
    min-height: 100vh;
    margin: 0 auto;
    background: #ffffff;
}

.driver-topbar {
    position: sticky;
    top: 0;
    z-index: 10;
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: 56px;
    padding: 10px 14px;
    background: #ffffff;
    border-bottom: 1px solid #e5e7eb;
}

.driver-content {
    padding: 12px;
    padding-bottom: calc(18px + env(safe-area-inset-bottom));
}

.driver-summary-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px;
}

.driver-summary-card,
.driver-trip-card,
.driver-parcel-card {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: #ffffff;
}

.driver-summary-card {
    padding: 10px;
}

.driver-trip-card,
.driver-parcel-card {
    padding: 12px;
}

.driver-action-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px;
}

.driver-action-grid .btn,
.driver-full-btn {
    min-height: 44px;
    white-space: normal;
}

@media (min-width: 768px) {
    .driver-app {
        border-left: 1px solid #e5e7eb;
        border-right: 1px solid #e5e7eb;
    }
}
```

- [ ] Import driver styles in `resources/sass/app.scss`.

```scss
@import "driver";
```

- [ ] Replace `resources/views/driver/trips/index.blade.php` with the mobile layout version.

```blade
@extends('layouts.driver')

@section('content')
<div class="driver-topbar">
    <div>
        <strong>งานของฉัน</strong>
        <div class="small text-muted">{{ auth()->user()->name }}</div>
    </div>
    <form action="{{ route('login.logout') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-sm btn-outline-secondary">ออก</button>
    </form>
</div>

<div class="driver-content">
    @forelse($trips as $trip)
        <a href="{{ route('driver.trips.show', $trip) }}" class="driver-trip-card d-block text-reset mb-2">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong>{{ $trip->code }}</strong>
                    <div class="small text-muted">{{ optional($trip->trip_date)->format('Y-m-d') }}</div>
                </div>
                <span class="badge {{ $trip->status_badge_class }}">{{ $trip->status_label }}</span>
            </div>
            <div class="mt-2 small">
                <i class="fas fa-box"></i> {{ number_format($trip->trip_items_count) }} พัสดุ
                @if($trip->area_name)
                    <span class="ml-2"><i class="fas fa-map-marker-alt"></i> {{ $trip->area_name }}</span>
                @endif
            </div>
        </a>
    @empty
        <div class="text-center text-muted py-5">ยังไม่มีรอบขนส่งที่ได้รับมอบหมาย</div>
    @endforelse

    {{ $trips->links() }}
</div>
@endsection
```

- [ ] Add a reusable render method in `DriverTripController` so admin preview and driver detail use the same summary data.

```php
private function renderTrip(Trip $trip, string $view)
{
    $trip->load([
        'tripItems.order',
        'tripItems.orderReceive',
    ]);

    $items = $trip->tripItems->sortBy('id');
    $delivered = $items->where('delivery_status', TripItem::DELIVERY_STATUS_DELIVERED)->count();
    $failed = $items->where('delivery_status', TripItem::DELIVERY_STATUS_FAILED)->count();
    $returned = $items->where('delivery_status', TripItem::DELIVERY_STATUS_RETURNED)->count();

    return view($view, [
        'data' => $trip,
        'items' => $items,
        'summary' => [
            'total_parcels' => $items->count(),
            'delivered_count' => $delivered,
            'failed_count' => $failed,
            'remaining_count' => max(0, $items->count() - $delivered - $failed - $returned),
            'total_cod_amount' => (float) $items->sum('cod_amount'),
            'collected_amount' => (float) $items->sum('collected_amount'),
        ],
        'deliveryStatusLabels' => TripItem::deliveryStatusLabels(),
        'paymentStatusLabels' => TripItem::paymentStatusLabels(),
        'failedReasons' => $this->failedReasons(),
        'readOnly' => $this->isReadOnly($trip),
    ]);
}
```

- [ ] Update `DriverTripController::show()` and `showDriverTrip()` to call `renderTrip()`.

```php
public function show(Trip $trip)
{
    return $this->renderTrip($trip, 'admin.trip.driver');
}

public function showDriverTrip(Trip $trip)
{
    $this->ensureDriverOwnsTrip($trip);

    return $this->renderTrip($trip, 'driver.trips.show');
}
```

- [ ] Create `resources/views/driver/trips/show.blade.php` for the mobile trip detail page.

- [ ] Create `resources/views/driver/trips/_parcel-card.blade.php` for one parcel card. Keep actions: call, map, tracking, delivered, returned, failed reason, COD paid.

- [ ] Keep `DriverTripController::show()` for admin preview route and render the old admin view through `renderTrip()`.

- [ ] Add `tests/Feature/DriverMobileViewFeatureTest.php`.

```php
<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverMobileViewFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_driver_dashboard_uses_mobile_driver_layout()
    {
        $driver = User::create([
            'username' => 'driver-mobile',
            'password' => 'password',
            'name' => 'Driver',
            'last_name' => 'Mobile',
            'email' => 'driver-mobile@example.com',
            'status' => 'active',
            'role_name' => User::ROLE_DRIVER,
        ]);

        Trip::create([
            'code' => 'RUN-20260607-0001',
            'trip_date' => '2026-06-07',
            'driver_user_id' => $driver->id,
            'status' => Trip::STATUS_IN_TRANSIT,
        ]);

        $this->actingAs($driver)
            ->get('/driver')
            ->assertOk()
            ->assertSee('class="driver-shell"', false)
            ->assertDontSee('main-sidebar')
            ->assertSee('RUN-20260607-0001');
    }
}
```

## Validation

Run inside Docker for PHP:

```bash
docker compose exec app php artisan test --filter=DriverMobileViewFeatureTest
```

Run frontend build:

```bash
npm run dev
```

Expected result: test passes and Sass builds without errors.

## Commit

```bash
git add resources/views/layouts/driver.blade.php resources/views/driver/trips/index.blade.php resources/views/driver/trips/show.blade.php resources/views/driver/trips/_parcel-card.blade.php resources/sass/_driver.scss resources/sass/app.scss app/Http/Controllers/DriverTripController.php tests/Feature/DriverMobileViewFeatureTest.php
git commit -m "feat: add mobile driver layout"
```

## Acceptance Criteria

- Driver pages render without AdminLTE sidebar/footer.
- Driver UI is a single vertical mobile column with max width for larger screens.
- Buttons are large enough for touch usage.
- Text does not overflow buttons/cards on narrow mobile width.
