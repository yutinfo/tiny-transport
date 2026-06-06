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
