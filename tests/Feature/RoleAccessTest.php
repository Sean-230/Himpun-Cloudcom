<?php

namespace Tests\Feature;

use App\Support\DummyData;
use Tests\TestCase;

/**
 * HOD sees everything. PJ gets a short summary and the running prokers they supervise.
 * Koordinator and anggota see the prokers they work in; only a koordinator may send division requests.
 */
class RoleAccessTest extends TestCase
{
    private function as(string $role, string $name): static
    {
        return $this->withoutVite()->withSession(['demo_role' => $role, 'demo_user' => $name]);
    }

    private function validRequest(array $override = []): array
    {
        return $override + [
            'proker' => 'Techfest UC 2026',
            'dari_divisi' => 'Event',
            'ke_divisi' => 'Inventory',
            'judul' => 'Pinjam 2 meja panjang',
            'tenggat' => '2999-01-01',
            'catatan' => 'Untuk registrasi',
        ];
    }

    public function test_hod_is_the_default_and_sees_everything(): void
    {
        $this->withoutVite()->get('/proker/baru')->assertOk();
        $this->as('hod', 'Clarissa Tan')->get('/dashboard')->assertOk()->assertSee('Semua proker');
        $this->as('hod', 'Clarissa Tan')->get('/proker/3')->assertOk();   // a proposal
        $this->as('hod', 'Clarissa Tan')->get('/proker/4')->assertOk();   // a finished one
        $this->as('hod', 'Clarissa Tan')->get('/request-divisi')->assertOk()->assertSee('hanya lihat');
    }

    public function test_hod_can_edit_but_not_send_requests(): void
    {
        $this->as('hod', 'Clarissa Tan')->get('/proker/1')->assertOk()->assertSee('canEdit: true', false);
        $this->as('hod', 'Clarissa Tan')->get('/request-divisi')->assertDontSee('Buat request baru');
        $this->as('hod', 'Clarissa Tan')->post('/request-divisi', $this->validRequest())->assertForbidden();
    }

    public function test_pj_gets_a_short_summary_of_the_running_prokers_they_supervise(): void
    {
        $this->as('pj', 'Michelle Wijaya')->get('/dashboard')->assertOk()
            ->assertSee('Proker yang kamu awasi')
            ->assertSee('Techfest UC 2026')
            ->assertDontSee('Semua proker')
            ->assertDontSee('Anggaran rencana');

        $this->as('pj', 'Michelle Wijaya')->get('/proker')->assertOk()
            ->assertSee('Techfest UC 2026')
            ->assertDontSee('Webinar Series');
    }

    public function test_pj_cannot_open_other_prokers_or_the_admin_pages(): void
    {
        $this->as('pj', 'Michelle Wijaya')->get('/proker/1')->assertOk();
        $this->as('pj', 'Michelle Wijaya')->get('/proker/5')->assertForbidden();   // Darren's
        $this->as('pj', 'Darren Lim')->get('/proker/3')->assertForbidden();        // his, but only a proposal
        $this->as('pj', 'Michelle Wijaya')->get('/proker/baru')->assertForbidden();
        $this->as('pj', 'Michelle Wijaya')->get('/request-divisi')->assertForbidden();
        $this->as('pj', 'Michelle Wijaya')->get('/proker/1')->assertSee('canEdit: false', false);
    }

    public function test_a_pj_with_nothing_running_sees_an_empty_state(): void
    {
        $this->as('pj', 'Natalia Hendra')->get('/dashboard')->assertOk()->assertSee('Belum ada proker berjalan');
        $this->as('pj', 'Natalia Hendra')->get('/proker')->assertOk()->assertSee('Belum ada proker berjalan');
    }

    public function test_koordinator_lands_on_their_own_prokers(): void
    {
        $this->as('koor', 'Felicia Hartono')->get('/dashboard')->assertRedirect('/proker');

        $this->as('koor', 'Felicia Hartono')->get('/proker')->assertOk()
            ->assertSee('Proker saya')
            ->assertSee('Koordinator Event')
            ->assertSee('Techfest UC 2026')
            ->assertDontSee('IMT Orientation Camp')
            ->assertDontSee('Anggaran terpakai');
    }

    public function test_koordinator_opens_only_prokers_they_work_in_read_only(): void
    {
        $this->as('koor', 'Felicia Hartono')->get('/proker/1')->assertOk()->assertSee('canEdit: false', false);
        $this->as('koor', 'Felicia Hartono')->get('/proker/2')->assertForbidden();
        $this->as('koor', 'Felicia Hartono')->get('/proker/baru')->assertForbidden();
    }

    public function test_koordinator_can_send_requests_only_from_a_division_they_lead(): void
    {
        $this->as('koor', 'Felicia Hartono')->get('/request-divisi')->assertOk()->assertSee('Buat request baru');

        $this->as('koor', 'Felicia Hartono')->post('/request-divisi', $this->validRequest())
            ->assertRedirect('/request-divisi')
            ->assertSessionHas('division_requests', fn ($r) => $r[0]['dari_koor'] === 'Felicia Hartono' && $r[0]['status'] === 'Menunggu');

        $this->as('koor', 'Felicia Hartono')->post('/request-divisi', $this->validRequest(['dari_divisi' => 'Tech']))->assertForbidden();
        $this->as('koor', 'Felicia Hartono')->post('/request-divisi', $this->validRequest(['proker' => 'IMT Orientation Camp']))->assertForbidden();
    }

    public function test_koordinator_sees_and_answers_only_their_own_requests(): void
    {
        // 101 Valerie -> Bryan, 102 Felicia -> Valerie, 103 Bryan -> Clarissa Putri, 104 Angeline -> Andreas
        $this->as('koor', 'Felicia Hartono')->get('/request-divisi')
            ->assertSee(DummyData::divisionRequests()[1]['judul'])      // 102, hers
            ->assertDontSee(DummyData::divisionRequests()[3]['judul'])   // 104, not hers
            ->assertDontSee(DummyData::divisionRequests()[2]['judul']);  // 103, not hers

        $this->as('koor', 'Valerie Susanto')->patch('/request-divisi/102/status', ['status' => 'Selesai'])
            ->assertRedirect('/request-divisi')
            ->assertSessionHas('division_requests', fn ($r) => collect($r)->firstWhere('id', 102)['status'] === 'Selesai');

        $this->as('koor', 'Felicia Hartono')->patch('/request-divisi/101/status', ['status' => 'Diproses'])->assertForbidden();
        $this->as('koor', 'Valerie Susanto')->patch('/request-divisi/102/status', ['status' => 'Bebas'])->assertSessionHasErrors('status');
    }

    public function test_anggota_sees_their_prokers_but_cannot_send_or_answer_requests(): void
    {
        $this->as('anggota', 'Timothy Lee')->get('/dashboard')->assertRedirect('/proker');
        $this->as('anggota', 'Timothy Lee')->get('/proker')->assertOk()->assertSee('Anggota Tech')->assertSee('Techfest UC 2026');
        $this->as('anggota', 'Timothy Lee')->get('/proker/1')->assertOk()->assertSee('canEdit: false', false);
        $this->as('anggota', 'Timothy Lee')->get('/proker/2')->assertForbidden();

        $this->as('anggota', 'Timothy Lee')->get('/request-divisi')->assertForbidden();
        $this->as('anggota', 'Timothy Lee')->post('/request-divisi', $this->validRequest())->assertForbidden();
        $this->as('anggota', 'Timothy Lee')->patch('/request-divisi/101/status', ['status' => 'Diproses'])->assertForbidden();
        $this->as('anggota', 'Timothy Lee')->get('/proker/baru')->assertForbidden();
    }

    public function test_the_menu_follows_the_role(): void
    {
        // "Oprec" and "Dokumen" are menu items only the HOD has
        $this->as('hod', 'Clarissa Tan')->get('/proker')->assertSee('Request divisi')->assertSee('Oprec')->assertSee('Dokumen');
        $this->as('pj', 'Michelle Wijaya')->get('/proker')->assertDontSee('Request divisi')->assertDontSee('Oprec')->assertDontSee('Proker saya');
        $this->as('koor', 'Felicia Hartono')->get('/proker')->assertSee('Request divisi')->assertSee('Proker saya')->assertDontSee('Oprec');
        $this->as('anggota', 'Timothy Lee')->get('/proker')->assertSee('Proker saya')->assertDontSee('Request divisi')->assertDontSee('Oprec');
    }

    public function test_a_forbidden_page_explains_itself_and_points_home(): void
    {
        $this->as('anggota', 'Timothy Lee')->get('/proker/baru')
            ->assertForbidden()
            ->assertSee('Halaman ini tidak tersedia untukmu')
            ->assertSee('Anggota')
            ->assertSee('Timothy Lee');
    }

    public function test_switching_role_stores_it_and_lands_on_that_roles_home(): void
    {
        $this->post('/peran', ['role' => 'koor', 'person' => 'Felicia Hartono'])
            ->assertRedirect('/proker')
            ->assertSessionHas(['demo_role' => 'koor', 'demo_user' => 'Felicia Hartono']);

        $this->post('/peran', ['role' => 'pj', 'person' => 'Michelle Wijaya'])->assertRedirect('/dashboard');
        $this->post('/peran', ['role' => 'hod', 'person' => 'Clarissa Tan'])->assertRedirect('/dashboard');
    }

    public function test_switching_rejects_unknown_roles_and_people_who_do_not_fit(): void
    {
        $this->post('/peran', ['role' => 'dosen', 'person' => 'Clarissa Tan'])->assertSessionHasErrors('role');
        $this->post('/peran', ['role' => 'hod', 'person' => 'Felicia Hartono'])->assertSessionHasErrors('person')->assertSessionMissing('demo_role');
        $this->post('/peran', ['role' => 'anggota', 'person' => 'Felicia Hartono'])->assertSessionHasErrors('person');
    }

    public function test_a_stale_session_falls_back_to_hod(): void
    {
        $this->withoutVite()->withSession(['demo_role' => 'koor', 'demo_user' => 'Clarissa Tan'])->get('/proker/baru')->assertOk();
    }

    public function test_every_listed_person_exists_for_their_role(): void
    {
        foreach (array_keys(DummyData::ROLES) as $role) {
            $this->assertNotEmpty(DummyData::peopleFor($role), "no people for {$role}");
        }
    }
}
