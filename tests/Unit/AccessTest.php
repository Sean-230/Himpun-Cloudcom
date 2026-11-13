<?php

namespace Tests\Unit;

use App\Support\Access;
use App\Support\DummyData;
use PHPUnit\Framework\TestCase;

class AccessTest extends TestCase
{
    private function me(string $role, string $name): array
    {
        return ['role' => $role, 'name' => $name, 'label' => DummyData::ROLES[$role]];
    }

    private function names(array $prokers): array
    {
        return array_column($prokers, 'nama');
    }

    public function test_everyone_who_can_be_picked_exists_and_fits_their_role(): void
    {
        $members = array_column(DummyData::members(), 'name');

        foreach (DummyData::ROLES as $role => $label) {
            foreach (DummyData::peopleFor($role) as $name) {
                $this->assertContains($name, $members);
            }
        }

        foreach (DummyData::peopleFor('anggota') as $name) {
            foreach (DummyData::prokers() as $p) {
                $this->assertNotContains(DummyData::roleIn($p, $name), ['K', 'Ko', 'PJ'], "{$name} is more than an anggota in {$p['nama']}");
            }
        }
    }

    public function test_the_hod_sees_every_proker_but_lists_only_the_running_ones(): void
    {
        $hod = $this->me('hod', 'Clarissa Tan');

        foreach (DummyData::prokers() as $p) {
            $this->assertTrue(Access::canSee($hod, $p));
        }
        $this->assertSame(['Techfest UC 2026', 'Webinar Series: Career Talk'], $this->names(Access::listFor($hod)));
    }

    public function test_a_pj_sees_only_running_prokers_they_supervise(): void
    {
        $this->assertSame(['Techfest UC 2026'], $this->names(Access::listFor($this->me('pj', 'Michelle Wijaya'))));
        // Darren supervises Webinar (running) and Hackathon (a proposal)
        $this->assertSame(['Webinar Series: Career Talk'], $this->names(Access::listFor($this->me('pj', 'Darren Lim'))));
        $this->assertSame([], Access::listFor($this->me('pj', 'Natalia Hendra')));
    }

    public function test_koordinator_and_anggota_see_the_prokers_they_work_in_in_any_status(): void
    {
        // Andreas: koordinator Tech in Techfest and Webinar, anggota in Orientation Camp
        $names = $this->names(Access::listFor($this->me('koor', 'Andreas Gunawan')));
        $this->assertEqualsCanonicalizing(['Techfest UC 2026', 'IMT Orientation Camp', 'Webinar Series: Career Talk'], $names);

        // Kenneth was in a finished proker too
        $kenneth = $this->names(Access::listFor($this->me('anggota', 'Kenneth Alamsyah')));
        $this->assertContains('IMT Class Meeting 2025', $kenneth);
    }

    public function test_pj_oversight_does_not_count_as_working_in_a_proker(): void
    {
        $techfest = DummyData::proker(1);

        $this->assertFalse(Access::canSee($this->me('koor', 'Michelle Wijaya'), $techfest));
    }

    public function test_role_labels_say_what_a_person_does(): void
    {
        $techfest = DummyData::proker(1);

        $this->assertSame('Ketupel', Access::roleLabel($techfest, 'Rafael Santoso'));
        $this->assertSame('PJ', Access::roleLabel($techfest, 'Michelle Wijaya'));
        $this->assertSame('Koordinator Event', Access::roleLabel($techfest, 'Felicia Hartono'));
        $this->assertSame('Anggota Tech', Access::roleLabel($techfest, 'Timothy Lee'));
        $this->assertSame('–', Access::roleLabel($techfest, 'Daniel Kurnia') === 'Anggota Inventory' ? '–' : 'x');
    }

    public function test_divisions_coordinated(): void
    {
        $this->assertSame(['Event'], Access::divisionsCoordinated(DummyData::proker(1), 'Felicia Hartono'));
        $this->assertSame([], Access::divisionsCoordinated(DummyData::proker(1), 'Timothy Lee'));
    }

    public function test_each_role_lands_on_its_own_home(): void
    {
        $this->assertSame('dashboard', Access::home($this->me('hod', 'Clarissa Tan')));
        $this->assertSame('dashboard', Access::home($this->me('pj', 'Michelle Wijaya')));
        $this->assertSame('proker.index', Access::home($this->me('koor', 'Felicia Hartono')));
        $this->assertSame('proker.index', Access::home($this->me('anggota', 'Timothy Lee')));
    }

    public function test_stats_add_up(): void
    {
        $s = DummyData::stats(DummyData::proker(1));

        $this->assertSame(7, $s['divisi']);
        $this->assertSame(25, $s['total']);
        $this->assertLessThanOrEqual($s['total'], $s['done'] + $s['overdue']);
        $this->assertSame(55, $s['spent']);
        $this->assertNull(DummyData::stats(DummyData::proker(3))['spent']);
    }
}
