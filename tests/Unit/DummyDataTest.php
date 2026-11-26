<?php

namespace Tests\Unit;

use App\Support\DummyData;
use PHPUnit\Framework\TestCase;

class DummyDataTest extends TestCase
{
    public function test_members_are_unique(): void
    {
        $names = array_column(DummyData::members(), 'name');

        $this->assertCount(42, $names);
        $this->assertSame($names, array_unique($names));
    }

    public function test_each_person_holds_one_role_per_proker(): void
    {
        $names = array_column(DummyData::members(), 'name');

        foreach (DummyData::prokers() as $p) {
            $this->assertNotSame($p['ketupel'], $p['pj'], "{$p['nama']}: Ketupel and PJ are one person");

            // One person, one role per proker: Ketupel, PJ, or a single koordinator/anggota slot.
            $everyone = array_filter([$p['ketupel'], $p['pj']]);
            foreach ($p['divisions'] as $d) {
                $everyone = [...$everyone, ...$d['koor'], ...$d['anggota']];
            }

            $this->assertSame($everyone, array_values(array_unique($everyone)), "{$p['nama']}: someone holds more than one role");
            $this->assertSame([], array_diff($everyone, $names), "{$p['nama']}: unknown person");
        }
    }

    public function test_every_division_of_a_proker_has_its_jobdesc(): void
    {
        foreach (DummyData::prokers() as $p) {
            foreach ($p['divisions'] as $d) {
                $this->assertNotEmpty($d['jobdesc'], "{$p['nama']} / {$d['name']}: no jobdesc");

                foreach ($d['jobdesc'] as $j) {
                    $this->assertNotSame('', trim($j['title']));
                    $this->assertIsInt($j['h']);
                    $this->assertIsBool($j['done']);
                }
            }
        }
    }

    public function test_division_defaults_carry_the_same_jobdesc(): void
    {
        foreach (DummyData::divisionDefs() as $def) {
            $this->assertSame(DummyData::jobdescFor($def['name']), $def['jobdesc']);
        }
    }

    public function test_weekly_meetings_are_well_formed(): void
    {
        $techfest = DummyData::proker(1);

        $this->assertCount(2, $techfest['rapat']);

        foreach (DummyData::prokers() as $p) {
            foreach ($p['rapat'] as $r) {
                $this->assertContains($r['scope'], ['pleno', 'koor']);
                $this->assertGreaterThanOrEqual(0, $r['day']);
                $this->assertLessThanOrEqual(6, $r['day']);
                $this->assertMatchesRegularExpression('/^\d{2}:\d{2}$/', $r['time']);
            }
        }
    }

    public function test_proker_lookup(): void
    {
        $this->assertSame('Techfest UC 2026', DummyData::proker(1)['nama']);
        $this->assertNull(DummyData::proker(999));
    }
}
