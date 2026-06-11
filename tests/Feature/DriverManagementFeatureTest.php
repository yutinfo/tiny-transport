<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverManagementFeatureTest extends TestCase
{
    use RefreshDatabase;

    private ?User $adminUser = null;
    private ?User $staffUser = null;

    private function admin(): User
    {
        return $this->adminUser ??= User::create([
            'username' => 'admin-dm',
            'password' => 'password',
            'name' => 'Admin',
            'last_name' => 'DM',
            'email' => 'admin-dm@example.com',
            'status' => 'active',
            'role_name' => User::ROLE_ADMIN,
        ]);
    }

    private function staff(): User
    {
        return $this->staffUser ??= User::create([
            'username' => 'staff-dm',
            'password' => 'password',
            'name' => 'Staff',
            'last_name' => 'DM',
            'email' => 'staff-dm@example.com',
            'status' => 'active',
            'role_name' => User::ROLE_STAFF,
        ]);
    }

    private function makeDriver(array $overrides = []): Driver
    {
        return Driver::create(array_merge([
            'code' => Driver::generateCode(),
            'name' => 'Test',
            'last_name' => 'Driver',
            'mobile' => '0890000000',
            'status' => Driver::STATUS_ACTIVE,
        ], $overrides));
    }

    public function test_driver_role_user_cannot_access_admin_drivers()
    {
        $driverUser = User::create([
            'username' => 'portal-driver',
            'password' => 'password',
            'name' => 'Portal',
            'last_name' => 'Driver',
            'email' => 'portal-driver@example.com',
            'status' => 'active',
            'role_name' => User::ROLE_DRIVER,
        ]);

        $driver = $this->makeDriver(['mobile' => '0800001111']);

        $this->actingAs($driverUser)->get('/admin/drivers')->assertForbidden();
        $this->actingAs($driverUser)->get('/admin/drivers/' . $driver->id)->assertForbidden();
        $this->actingAs($driverUser)->get('/admin/drivers/create')->assertForbidden();
        $this->actingAs($driverUser)->post('/admin/drivers', [])->assertForbidden();
    }

    public function test_admin_can_view_create_and_staff_cannot()
    {
        $this->actingAs($this->admin())->get('/admin/drivers/create')->assertOk();
        $this->actingAs($this->staff())->get('/admin/drivers/create')->assertForbidden();
    }

    public function test_staff_can_view_index_and_show()
    {
        $driver = $this->makeDriver();

        $this->actingAs($this->staff())->get('/admin/drivers')->assertOk()->assertSee($driver->code);
        $this->actingAs($this->staff())->get('/admin/drivers/' . $driver->id)->assertOk()->assertSee($driver->full_name);
    }

    public function test_staff_cannot_store_edit_or_delete()
    {
        $driver = $this->makeDriver();

        $this->actingAs($this->staff())->post('/admin/drivers', [
            'name' => 'Nope', 'mobile' => '0890000111', 'status' => Driver::STATUS_ACTIVE,
        ])->assertForbidden();

        $this->actingAs($this->staff())->get('/admin/drivers/' . $driver->id . '/edit')->assertForbidden();
        $this->actingAs($this->staff())->delete('/admin/drivers/' . $driver->id)->assertForbidden();
    }

    public function test_admin_can_create_driver_without_account()
    {
        $this->actingAs($this->admin())->post('/admin/drivers', [
            'name' => 'Somchai',
            'last_name' => 'Jaidee',
            'mobile' => '0811112222',
            'license_plate' => '1กก-9999',
            'status' => Driver::STATUS_ACTIVE,
            'account_mode' => 'none',
        ])->assertRedirect();

        $this->assertDatabaseHas('drivers', [
            'name' => 'Somchai',
            'mobile' => '0811112222',
            'user_id' => null,
        ]);
    }

    public function test_create_with_new_account_creates_user_and_driver()
    {
        $this->actingAs($this->admin())->post('/admin/drivers', [
            'name' => 'New',
            'last_name' => 'Account',
            'mobile' => '0822223333',
            'status' => Driver::STATUS_ACTIVE,
            'account_mode' => 'create',
            'username' => 'newdriveracct',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'email' => 'newdriveracct@example.com',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'username' => 'newdriveracct',
            'role_name' => User::ROLE_DRIVER,
            'status' => 'active',
        ]);

        $user = User::where('username', 'newdriveracct')->first();
        $this->assertDatabaseHas('drivers', [
            'mobile' => '0822223333',
            'user_id' => $user->id,
        ]);

        // The new account can log into the driver portal.
        $this->post('/login', [
            'username' => 'newdriveracct',
            'password' => 'secret123',
        ])->assertRedirect('/driver');
    }

    public function test_link_existing_driver_account()
    {
        $driverUser = User::create([
            'username' => 'linkme',
            'password' => 'password',
            'name' => 'Link',
            'last_name' => 'Me',
            'email' => 'linkme@example.com',
            'status' => 'active',
            'role_name' => User::ROLE_DRIVER,
        ]);

        $driver = $this->makeDriver(['mobile' => '0833334444']);

        $this->actingAs($this->admin())->put('/admin/drivers/' . $driver->id, [
            'name' => $driver->name,
            'last_name' => $driver->last_name,
            'mobile' => $driver->mobile,
            'status' => Driver::STATUS_ACTIVE,
            'account_mode' => 'link',
            'user_id' => $driverUser->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('drivers', [
            'id' => $driver->id,
            'user_id' => $driverUser->id,
        ]);
    }

    public function test_linked_account_is_excluded_from_link_dropdown_for_others()
    {
        $driverUser = User::create([
            'username' => 'alreadylinked',
            'password' => 'password',
            'name' => 'Already',
            'last_name' => 'Linked',
            'email' => 'alreadylinked@example.com',
            'status' => 'active',
            'role_name' => User::ROLE_DRIVER,
        ]);

        $this->makeDriver(['mobile' => '0844445555', 'user_id' => $driverUser->id]);
        $other = $this->makeDriver(['mobile' => '0855556666']);

        $this->actingAs($this->admin())
            ->get('/admin/drivers/' . $other->id . '/edit')
            ->assertOk()
            ->assertDontSee('alreadylinked');
    }

    public function test_deactivating_driver_disables_linked_user_login()
    {
        $driverUser = User::create([
            'username' => 'tobeoff',
            'password' => 'password',
            'name' => 'To',
            'last_name' => 'Off',
            'email' => 'tobeoff@example.com',
            'status' => 'active',
            'role_name' => User::ROLE_DRIVER,
        ]);

        $driver = $this->makeDriver(['mobile' => '0866667777', 'user_id' => $driverUser->id]);

        $this->actingAs($this->admin())
            ->post('/admin/drivers/' . $driver->id . '/toggle-status')
            ->assertRedirect();

        $this->assertDatabaseHas('drivers', ['id' => $driver->id, 'status' => Driver::STATUS_INACTIVE]);
        $this->assertDatabaseHas('users', ['id' => $driverUser->id, 'status' => 'inactive']);

        // A deactivated linked account can no longer log in.
        $this->post('/login', [
            'username' => 'tobeoff',
            'password' => 'password',
        ])->assertRedirect('/login');
    }

    public function test_delete_blocked_when_driver_has_trips()
    {
        $driver = $this->makeDriver(['mobile' => '0877778888']);

        Trip::create([
            'code' => 'RUN-20260611-0001',
            'trip_date' => '2026-06-11',
            'driver_id' => $driver->id,
            'status' => Trip::STATUS_DRAFT,
        ]);

        $this->actingAs($this->admin())
            ->delete('/admin/drivers/' . $driver->id)
            ->assertRedirect()
            ->assertSessionHasErrors('driver');

        $this->assertDatabaseHas('drivers', ['id' => $driver->id]);
    }

    public function test_delete_succeeds_when_no_trips()
    {
        $driver = $this->makeDriver(['mobile' => '0888889999']);

        $this->actingAs($this->admin())
            ->delete('/admin/drivers/' . $driver->id)
            ->assertRedirect('/admin/drivers');

        $this->assertDatabaseMissing('drivers', ['id' => $driver->id]);
    }

    public function test_reset_password_updates_linked_user()
    {
        $driverUser = User::create([
            'username' => 'resetme',
            'password' => 'password',
            'name' => 'Reset',
            'last_name' => 'Me',
            'email' => 'resetme@example.com',
            'status' => 'active',
            'role_name' => User::ROLE_DRIVER,
        ]);

        $driver = $this->makeDriver(['mobile' => '0899990000', 'user_id' => $driverUser->id]);

        $this->actingAs($this->admin())
            ->post('/admin/drivers/' . $driver->id . '/reset-password', [
                'password' => 'brandnew123',
                'password_confirmation' => 'brandnew123',
            ])
            ->assertRedirect();

        $this->post('/login', [
            'username' => 'resetme',
            'password' => 'brandnew123',
        ])->assertRedirect('/driver');
    }

    public function test_backfill_command_is_idempotent()
    {
        $driverUser = User::create([
            'username' => 'backfillme',
            'password' => 'password',
            'name' => 'Backfill',
            'last_name' => 'Me',
            'email' => 'backfillme@example.com',
            'status' => 'active',
            'role_name' => User::ROLE_DRIVER,
        ]);

        Trip::create([
            'code' => 'RUN-20260611-0002',
            'trip_date' => '2026-06-11',
            'driver_user_id' => $driverUser->id,
            'driver_mobile' => '0901112222',
            'status' => Trip::STATUS_DRAFT,
        ]);

        $this->artisan('drivers:backfill')->assertExitCode(0);
        $countAfterFirst = Driver::count();

        $this->artisan('drivers:backfill')->assertExitCode(0);
        $this->assertSame($countAfterFirst, Driver::count());

        // Trip got linked to the created driver.
        $driver = Driver::where('user_id', $driverUser->id)->first();
        $this->assertNotNull($driver);
        $this->assertDatabaseHas('trips', [
            'driver_user_id' => $driverUser->id,
            'driver_id' => $driver->id,
        ]);
    }
}
