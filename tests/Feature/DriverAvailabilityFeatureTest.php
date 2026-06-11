<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverAvailabilityFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'username' => 'admin-av',
            'password' => 'password',
            'name' => 'Admin',
            'last_name' => 'Av',
            'email' => 'admin-av@example.com',
            'status' => 'active',
            'role_name' => User::ROLE_ADMIN,
        ]);
    }

    private function makeDriver(string $mobile): Driver
    {
        return Driver::create([
            'code' => Driver::generateCode(),
            'name' => 'Driver',
            'last_name' => $mobile,
            'mobile' => $mobile,
            'status' => Driver::STATUS_ACTIVE,
        ]);
    }

    private function makeTrip(Driver $driver, string $date, string $status, int $seq): Trip
    {
        return Trip::create([
            'code' => 'RUN-' . str_replace('-', '', $date) . '-' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT),
            'trip_date' => $date,
            'driver_id' => $driver->id,
            'status' => $status,
        ]);
    }

    public function test_guest_is_redirected()
    {
        $this->get('/admin/api/drivers/availability?date=2026-06-11')
            ->assertRedirect('/login');
    }

    public function test_busy_when_driver_has_active_trip_on_date()
    {
        $driver = $this->makeDriver('0890000001');
        $this->makeTrip($driver, '2026-06-11', Trip::STATUS_ASSIGNED, 1);

        $response = $this->actingAs($this->admin())
            ->getJson('/admin/api/drivers/availability?date=2026-06-11')
            ->assertOk();

        $row = collect($response->json())->firstWhere('driver_id', $driver->id);
        $this->assertTrue($row['busy']);
        $this->assertSame('RUN-20260611-0001', $row['trips'][0]['code']);
    }

    public function test_not_busy_on_a_different_date()
    {
        $driver = $this->makeDriver('0890000002');
        $this->makeTrip($driver, '2026-06-11', Trip::STATUS_ASSIGNED, 1);

        $response = $this->actingAs($this->admin())
            ->getJson('/admin/api/drivers/availability?date=2026-06-12')
            ->assertOk();

        $row = collect($response->json())->firstWhere('driver_id', $driver->id);
        $this->assertFalse($row['busy']);
    }

    public function test_cancelled_trips_do_not_count_as_busy()
    {
        $driver = $this->makeDriver('0890000003');
        $this->makeTrip($driver, '2026-06-11', Trip::STATUS_CANCELLED, 1);

        $response = $this->actingAs($this->admin())
            ->getJson('/admin/api/drivers/availability?date=2026-06-11')
            ->assertOk();

        $row = collect($response->json())->firstWhere('driver_id', $driver->id);
        $this->assertFalse($row['busy']);
    }

    public function test_exclude_trip_param_ignores_that_trip()
    {
        $driver = $this->makeDriver('0890000004');
        $trip = $this->makeTrip($driver, '2026-06-11', Trip::STATUS_ASSIGNED, 1);

        $response = $this->actingAs($this->admin())
            ->getJson('/admin/api/drivers/availability?date=2026-06-11&exclude_trip=' . $trip->id)
            ->assertOk();

        $row = collect($response->json())->firstWhere('driver_id', $driver->id);
        $this->assertFalse($row['busy']);
    }

    public function test_store_trip_for_busy_driver_requires_confirm_busy()
    {
        $admin = $this->admin();
        $driver = $this->makeDriver('0890000005');
        $this->makeTrip($driver, '2026-06-11', Trip::STATUS_ASSIGNED, 1);

        // Without confirm_busy -> validation error.
        $this->actingAs($admin)->post('/admin/trips', [
            'trip_date' => '2026-06-11',
            'driver_id' => $driver->id,
        ])->assertSessionHasErrors('driver_id');

        // With confirm_busy -> allowed and snapshot populated from the master driver.
        $this->actingAs($admin)->post('/admin/trips', [
            'trip_date' => '2026-06-11',
            'driver_id' => $driver->id,
            'confirm_busy' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('trips', [
            'driver_id' => $driver->id,
            'driver_name' => $driver->full_name,
            'driver_mobile' => $driver->mobile,
            'trip_date' => '2026-06-11',
            'status' => Trip::STATUS_DRAFT,
        ]);
    }

    public function test_store_trip_with_driver_id_populates_snapshot_and_user_link()
    {
        $admin = $this->admin();

        $driverUser = User::create([
            'username' => 'avdriver',
            'password' => 'password',
            'name' => 'Av',
            'last_name' => 'Driver',
            'email' => 'avdriver@example.com',
            'status' => 'active',
            'role_name' => User::ROLE_DRIVER,
        ]);

        $driver = Driver::create([
            'code' => Driver::generateCode(),
            'name' => 'Snap',
            'last_name' => 'Shot',
            'mobile' => '0890000006',
            'license_plate' => 'CAR-9',
            'area_name' => 'Zone A',
            'status' => Driver::STATUS_ACTIVE,
            'user_id' => $driverUser->id,
        ]);

        $this->actingAs($admin)->post('/admin/trips', [
            'trip_date' => '2026-06-13',
            'driver_id' => $driver->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('trips', [
            'driver_id' => $driver->id,
            'driver_user_id' => $driverUser->id,
            'driver_name' => 'Snap Shot',
            'driver_mobile' => '0890000006',
            'car_id' => 'CAR-9',
            'area_name' => 'Zone A',
        ]);
    }
}
