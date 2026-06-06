<?php
 
namespace Tests\Feature;
 
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
 
class DriverRoleFeatureTest extends TestCase
{
    use RefreshDatabase;
 
    public function test_admin_can_create_driver_user()
    {
        $admin = User::create([
            'username' => 'admin-driver-role',
            'password' => 'password',
            'name' => 'Admin',
            'last_name' => 'Role',
            'email' => 'admin-driver-role@example.com',
            'status' => 'active',
            'role_name' => User::ROLE_ADMIN,
        ]);
 
        $this->actingAs($admin)
            ->post('/admin/users/store', [
                'username' => 'driver-one',
                'password' => 'password',
                'name' => 'Driver',
                'last_name' => 'One',
                'email' => 'driver-one@example.com',
                'status' => 'active',
                'role_name' => User::ROLE_DRIVER,
            ])
            ->assertRedirect('/admin/users/create');
 
        $this->assertDatabaseHas('users', [
            'username' => 'driver-one',
            'role_name' => User::ROLE_DRIVER,
        ]);
    }
 
    public function test_user_form_shows_driver_role_option()
    {
        $admin = User::create([
            'username' => 'admin-driver-form',
            'password' => 'password',
            'name' => 'Admin',
            'last_name' => 'Form',
            'email' => 'admin-driver-form@example.com',
            'status' => 'active',
            'role_name' => User::ROLE_ADMIN,
        ]);
 
        $this->actingAs($admin)
            ->get('/admin/users/create')
            ->assertOk()
            ->assertSee('คนขับรถ');
    }
}
