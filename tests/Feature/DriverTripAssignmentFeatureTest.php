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
}
