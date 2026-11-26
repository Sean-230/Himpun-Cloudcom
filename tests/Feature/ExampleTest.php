<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_home_redirects_to_the_dashboard(): void
    {
        $this->get('/')->assertRedirect('/dashboard');
    }

    public function test_the_dashboard_loads(): void
    {
        // CI runs tests before building assets, so there is no Vite manifest yet.
        $this->withoutVite()->get('/dashboard')->assertOk();
    }

    public function test_the_proker_form_loads(): void
    {
        $this->withoutVite()->get('/proker/baru')->assertOk()->assertSee('Buat proker');
    }
}
