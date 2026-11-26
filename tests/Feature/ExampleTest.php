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

    public function test_the_proker_detail_shows_its_jobdesc_and_meetings(): void
    {
        $this->withoutVite()->get('/proker/1')
            ->assertOk()
            ->assertSee('Techfest UC 2026')
            ->assertSee('Jobdesc dan target per divisi')
            ->assertSee('Jadwal rapat mingguan');
    }

    public function test_an_unknown_proker_is_a_404(): void
    {
        $this->withoutVite()->get('/proker/999')->assertNotFound();
    }

    public function test_the_proker_list_shows_only_running_prokers(): void
    {
        $this->withoutVite()->get('/proker')
            ->assertOk()
            ->assertSee('Techfest UC 2026')
            ->assertSee('Webinar Series: Career Talk')
            ->assertDontSee('National Hackathon UC')
            ->assertDontSee('IMT Orientation Camp')
            ->assertDontSee('Alumni Night IMT')
            ->assertDontSee('IMT Class Meeting 2025');
    }
}
