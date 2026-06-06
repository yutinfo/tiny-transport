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
