<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverHistoryProfileFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function makeDriver(string $suffix): User
    {
        return User::create([
            'username' => 'driver-' . $suffix,
            'password' => 'password',
            'name' => 'Driver ' . $suffix,
            'last_name' => 'Test',
            'email' => 'driver-' . $suffix . '@example.com',
            'status' => 'active',
            'role_name' => User::ROLE_DRIVER,
        ]);
    }

    public function test_driver_history_shows_only_own_closed_trips()
    {
        $driver = $this->makeDriver('history');

        $ownCompleted = Trip::create([
            'code' => 'RUN-HIST-0001',
            'trip_date' => '2026-06-07',
            'driver_user_id' => $driver->id,
            'status' => Trip::STATUS_COMPLETED,
        ]);

        $ownActive = Trip::create([
            'code' => 'RUN-HIST-0002',
            'trip_date' => '2026-06-07',
            'driver_user_id' => $driver->id,
            'status' => Trip::STATUS_IN_TRANSIT,
        ]);

        $otherCompleted = Trip::create([
            'code' => 'RUN-HIST-0003',
            'trip_date' => '2026-06-07',
            'driver_user_id' => $this->makeDriver('other')->id,
            'status' => Trip::STATUS_COMPLETED,
        ]);

        $this->actingAs($driver)
            ->get('/driver/trips/history')
            ->assertOk()
            ->assertSee('RUN-HIST-0001')      // own + closed → shown
            ->assertDontSee('RUN-HIST-0002')  // own but active → hidden
            ->assertDontSee('RUN-HIST-0003'); // closed but another driver → hidden
    }

    public function test_history_route_is_not_captured_by_trip_wildcard()
    {
        // /trips/history must resolve to the history screen (200), not be treated
        // as /trips/{trip} with id "history" (which would 404/403).
        $driver = $this->makeDriver('order');

        $this->actingAs($driver)
            ->get('/driver/trips/history')
            ->assertOk();
    }

    public function test_driver_can_open_profile()
    {
        $driver = $this->makeDriver('profile');

        $this->actingAs($driver)
            ->get('/driver/profile')
            ->assertOk()
            ->assertSee($driver->name);
    }

    public function test_non_driver_cannot_open_driver_history_or_profile()
    {
        $admin = User::create([
            'username' => 'admin-no-driver',
            'password' => 'password',
            'name' => 'Admin',
            'last_name' => 'NoDriver',
            'email' => 'admin-no-driver@example.com',
            'status' => 'active',
            'role_name' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin)->get('/driver/trips/history')->assertForbidden();
        $this->actingAs($admin)->get('/driver/profile')->assertForbidden();
    }
}
