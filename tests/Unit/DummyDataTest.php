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
}
