# Task 003: Admin Trip Form Driver Selection

## Goal

ให้ admin/staff เลือกคนขับจาก user role `driver` ตอนสร้างหรือแก้ไขรอบขนส่ง และให้ระบบเติมชื่อคนขับจาก user ที่เลือก

## Decision

ใช้ select แบบ server-rendered ใน Blade ก่อน ไม่เพิ่ม Select2/AJAX เพราะจำนวนคนขับคาดว่ายังไม่มาก และต้องคุม scope ให้เล็ก

## Files

- Modify: `app/Http/Controllers/TripController.php`
- Modify: `resources/views/admin/trip/form.blade.php`
- Test: `tests/Feature/DriverTripAssignmentFeatureTest.php`

## Steps

- [ ] Update `TripController::create()` and `TripController::edit()` to pass active driver users.

```php
use App\Models\User;
```

```php
'drivers' => User::query()
    ->where('role_name', User::ROLE_DRIVER)
    ->where('status', 'active')
    ->orderBy('name')
    ->orderBy('last_name')
    ->get(),
```

- [ ] Update `TripController::validatedTripData()` to accept only active driver users.

```php
'driver_user_id' => [
    'nullable',
    Rule::exists('users', 'id')->where(function ($query) {
        $query->where('role_name', User::ROLE_DRIVER)
            ->where('status', 'active');
    }),
],
```

- [ ] Add a private method in `TripController` to merge selected driver data into trip data.

```php
private function applySelectedDriver(array $data): array
{
    if (empty($data['driver_user_id'])) {
        return $data;
    }

    $driver = User::find($data['driver_user_id']);

    if (! $driver) {
        return $data;
    }

    $data['driver_name'] = trim($driver->name . ' ' . $driver->last_name);

    return $data;
}
```

- [ ] Call `applySelectedDriver()` in `store()` and `update()` after validation and before persistence.

```php
$data = $this->applySelectedDriver($this->validatedTripData($request));
```

- [ ] Update `resources/views/admin/trip/form.blade.php` to show a driver select above the free-text driver fields.

```blade
<div class="col-md-3">
    <div class="form-group">
        <label for="driver_user_id">บัญชีคนขับรถ</label>
        <select name="driver_user_id" id="driver_user_id" class="form-control">
            <option value="">-- เลือกคนขับ --</option>
            @foreach($drivers ?? [] as $driver)
                <option value="{{ $driver->id }}" {{ (string) old('driver_user_id', $data->driver_user_id) === (string) $driver->id ? 'selected' : '' }}>
                    {{ trim($driver->name . ' ' . $driver->last_name) }}
                </option>
            @endforeach
        </select>
    </div>
</div>
```

- [ ] Keep `driver_name`, `driver_mobile`, and `car_id` fields visible. They remain useful as trip snapshots and vehicle information.

- [ ] Extend `tests/Feature/DriverTripAssignmentFeatureTest.php`.

```php
public function test_admin_can_assign_driver_user_when_creating_trip()
{
    $admin = User::create([
        'username' => 'admin-trip-driver',
        'password' => 'password',
        'name' => 'Admin',
        'last_name' => 'Trip',
        'email' => 'admin-trip-driver@example.com',
        'status' => 'active',
        'role_name' => User::ROLE_ADMIN,
    ]);

    $driver = User::create([
        'username' => 'driver-trip-create',
        'password' => 'password',
        'name' => 'Somchai',
        'last_name' => 'Driver',
        'email' => 'driver-trip-create@example.com',
        'status' => 'active',
        'role_name' => User::ROLE_DRIVER,
    ]);

    $this->actingAs($admin)
        ->post('/admin/trips', [
            'trip_date' => '2026-06-07',
            'driver_user_id' => $driver->id,
            'driver_mobile' => '0811111111',
            'car_id' => '1กก1234',
            'area_name' => 'กรุงเทพ',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('trips', [
        'driver_user_id' => $driver->id,
        'driver_name' => 'Somchai Driver',
        'driver_mobile' => '0811111111',
    ]);
}

public function test_staff_cannot_assign_non_driver_user_to_trip()
{
    $admin = User::create([
        'username' => 'admin-trip-non-driver',
        'password' => 'password',
        'name' => 'Admin',
        'last_name' => 'Trip',
        'email' => 'admin-trip-non-driver@example.com',
        'status' => 'active',
        'role_name' => User::ROLE_ADMIN,
    ]);

    $staff = User::create([
        'username' => 'staff-trip-non-driver',
        'password' => 'password',
        'name' => 'Staff',
        'last_name' => 'Trip',
        'email' => 'staff-trip-non-driver@example.com',
        'status' => 'active',
        'role_name' => User::ROLE_STAFF,
    ]);

    $this->actingAs($admin)
        ->from('/admin/trips/create')
        ->post('/admin/trips', [
            'trip_date' => '2026-06-07',
            'driver_user_id' => $staff->id,
        ])
        ->assertSessionHasErrors('driver_user_id');
}
```

## Validation

Run inside Docker:

```bash
docker compose exec app php artisan test --filter=DriverTripAssignmentFeatureTest
```

Expected result: all driver trip assignment tests pass.

## Commit

```bash
git add app/Http/Controllers/TripController.php resources/views/admin/trip/form.blade.php tests/Feature/DriverTripAssignmentFeatureTest.php
git commit -m "feat: assign driver users to trips"
```

## Acceptance Criteria

- Create/edit trip screens show active driver accounts.
- Admin/staff can assign only active users with role `driver`.
- Selected driver user is saved in `trips.driver_user_id`.
- Existing text fields remain available for display and vehicle data.
