<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_tracking_page_renders_shell(): void
    {
        $response = $this->get('/tracking');

        $response->assertOk();
        // Vue mount point + brand injection are present in the shell.
        $response->assertSee('id="tracking-app"', false);
        $response->assertSee('window.__BRAND', false);
        // Loads its own Mix bundle (renamed from web.js → tracking.js).
        $response->assertSee('js/tracking.js', false);
    }

    public function test_tracking_page_with_query_renders_shell(): void
    {
        // ?q= auto-search is client-side; the shell must still serve 200 with it.
        $this->get('/tracking?q=P2026WEB1')
            ->assertOk()
            ->assertSee('id="tracking-app"', false);
    }

    public function test_brand_name_comes_from_config(): void
    {
        // Rename contract: the company name is read from config('app.name') only,
        // so changing it must flow through to the rendered shell (title + __BRAND).
        config(['app.name' => 'RENAMED']);

        $this->get('/tracking')->assertSee('RENAMED', false);
    }

    public function test_legacy_web_path_redirects_to_tracking(): void
    {
        $this->get('/web')
            ->assertStatus(301)
            ->assertRedirect('/tracking');
    }

    public function test_legacy_web_path_preserves_query_string(): void
    {
        // Old shared links / printed QR codes carry ?q=CODE — it must survive.
        $this->get('/web?q=ABC')
            ->assertStatus(301)
            ->assertRedirect('/tracking?q=ABC');
    }
}
