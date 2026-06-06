# Task 002: Trip Driver Assignment Data Model

## Goal

ผูกคนขับที่ล็อกอินได้กับรอบขนส่งผ่าน `trips.driver_user_id` โดยยังเก็บ `driver_name`, `driver_mobile`, และ `car_id` เดิมไว้เพื่อรองรับข้อมูลเก่า

## Decision

เพิ่ม foreign key แบบ nullable ไปที่ `users.id` เฉพาะผู้ใช้ role `driver` จะถูกเลือกในฟอร์มงานถัดไป ข้อมูล text เดิมยังใช้เป็น snapshot เพื่อแสดงชื่อ/เบอร์/ทะเบียนรถในรายงานและข้อมูลเก่า

## Files

- Create: `database/migrations/2026_06_07_000001_add_driver_user_id_to_trips_table.php`
- Modify: `app/Models/Trip.php`
- Modify: `app/Models/User.php`
- Test: `tests/Feature/DriverTripAssignmentFeatureTest.php`

## Steps

- [ ] Create migration `database/migrations/2026_06_07_000001_add_driver_user_id_to_trips_table.php`.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->foreignId('driver_user_id')
                ->nullable()
                ->after('trip_date')
                ->constrained('users')
                ->nullOnDelete();

            $table->index(['driver_user_id', 'trip_date']);
        });
    }

    public function down()
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropForeign(['driver_user_id']);
            $table->dropIndex(['driver_user_id', 'trip_date']);
            $table->dropColumn('driver_user_id');
        });
    }
};
```

- [ ] Add `driver_user_id` to `Trip::$casts` and `Trip::$fillable`.

```php
'driver_user_id' => 'int',
```

```php
'driver_user_id',
```

- [ ] Add a relationship to `Trip`.

```php
public function driver()
{
    return $this->belongsTo(User::class, 'driver_user_id');
}
```

- [ ] Add a relationship to `User`.

```php
public function assignedTrips()
{
    return $this->hasMany(Trip::class, 'driver_user_id');
}
```

- [ ] Add `tests/Feature/DriverTripAssignmentFeatureTest.php`.

```php
<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverTripAssignmentFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_trip_can_belong_to_driver_user()
    {
        $driver = User::create([
            'username' => 'driver-assigned',
            'password' => 'password',
            'name' => 'Driver',
            'last_name' => 'Assigned',
            'email' => 'driver-assigned@example.com',
            'status' => 'active',
            'role_name' => User::ROLE_DRIVER,
        ]);

        $trip = Trip::create([
            'code' => 'RUN-20260607-0001',
            'trip_date' => '2026-06-07',
            'driver_user_id' => $driver->id,
            'driver_name' => 'Driver Assigned',
            'driver_mobile' => '0811111111',
            'status' => Trip::STATUS_DRAFT,
        ]);

        $this->assertSame($driver->id, $trip->driver->id);
        $this->assertTrue($driver->assignedTrips()->whereKey($trip->id)->exists());
    }
}
```

## Validation

Run inside Docker:

```bash
docker compose exec app php artisan test --filter=DriverTripAssignmentFeatureTest
```

Expected result: the relationship test passes.

## Commit

```bash
git add database/migrations/2026_06_07_000001_add_driver_user_id_to_trips_table.php app/Models/Trip.php app/Models/User.php tests/Feature/DriverTripAssignmentFeatureTest.php
git commit -m "feat: link trips to driver users"
```

## Acceptance Criteria

- `trips.driver_user_id` can be empty for old trips.
- A trip can resolve its assigned driver user.
- A driver user can list assigned trips.
- Deleting a driver user keeps the trip and clears `driver_user_id`.
