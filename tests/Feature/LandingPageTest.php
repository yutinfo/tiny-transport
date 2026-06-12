<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_landing_page(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        // Vue mount point + brand injection are present in the shell.
        $response->assertSee('id="landing-app"', false);
        $response->assertSee('window.__BRAND', false);
        // Public links live in the shell/noscript so guests + crawlers find them.
        $response->assertSee('/login', false);
        $response->assertSee('/tracking', false);
        $response->assertSee('js/landing.js', false);
    }

    public function test_authed_admin_is_redirected_to_admin(): void
    {
        $admin = $this->makeUser('landing-admin', User::ROLE_ADMIN);

        $this->actingAs($admin)
            ->get('/')
            ->assertRedirect('admin');
    }

    public function test_authed_staff_is_redirected_to_admin(): void
    {
        $staff = $this->makeUser('landing-staff', User::ROLE_STAFF);

        $this->actingAs($staff)
            ->get('/')
            ->assertRedirect('admin');
    }

    public function test_authed_driver_is_redirected_to_driver_dashboard(): void
    {
        $driver = $this->makeUser('landing-driver', User::ROLE_DRIVER);

        $this->actingAs($driver)
            ->get('/')
            ->assertRedirect(route('driver.dashboard'));
    }

    public function test_brand_name_comes_from_config(): void
    {
        // Rename contract: the company name is read from config('app.name') only,
        // so changing it must flow through to the rendered shell.
        config(['app.name' => 'RENAMED']);

        $this->get('/')->assertSee('RENAMED', false);
    }

    private function makeUser(string $username, string $role): User
    {
        return User::create([
            'username' => $username,
            'password' => 'password',
            'name' => 'Landing',
            'last_name' => 'Tester',
            'email' => $username . '@example.com',
            'status' => 'active',
            'role_name' => $role,
        ]);
    }
}
