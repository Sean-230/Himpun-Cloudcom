<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\Jobdesc;
use App\Models\Membership;
use App\Models\Organisation;
use App\Models\Term;
use App\Models\User;
use App\Support\DummyData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OrganisationSeeder extends Seeder
{
    public function run(): void
    {
        $org = Organisation::firstOrCreate(
            ['slug' => 'su-imt'],
            [
                'name' => 'SU IMT',
                'type' => 'himpunan',
                'faculty' => 'School of Information Technology',
            ]
        );

        $term = Term::firstOrCreate(
            ['organisation_id' => $org->id, 'name' => '2025/2026'],
            [
                'starts_on' => '2025-08-01',
                'ends_on' => '2026-07-31',
                'is_active' => true,
            ]
        );

        $standingDivisions = [
            'Tech',
            'PDD Design',
            'PDD Dokum',
            'Event',
            'Social Relation',
            'Properties',
        ];

        $divisions = [];
        foreach ($standingDivisions as $divName) {
            $divisions[$divName] = Division::firstOrCreate([
                'organisation_id' => $org->id,
                'name' => $divName,
            ]);
        }

        $defaultPassword = Hash::make('password');

        foreach (DummyData::members() as $member) {
            $isHod = (bool) $member['hod'];
            $division = $isHod ? null : ($divisions[$member['divisi']] ?? null);

            $level = match (true) {
                $isHod => 'hod',
                $member['jabatan'] === 'Koordinator' => 'koordinator',
                default => 'staff',
            };

            $jobdesc = Jobdesc::firstOrCreate(
                [
                    'organisation_id' => $org->id,
                    'division_id' => $division?->id,
                    'title' => $member['jabatan'],
                    'level' => $level,
                ],
                [
                    'description' => $isHod
                        ? "Executive Board — {$member['jabatan']}"
                        : "{$member['jabatan']} Divisi {$member['divisi']}",
                ]
            );

            $emailSlug = Str::slug($member['name'], '.');

            $user = User::updateOrCreate(
                ['nim' => $member['nim']],
                [
                    'name' => $member['name'],
                    'email' => "{$emailSlug}@student.ciputra.ac.id",
                    'password' => $defaultPassword,
                ]
            );

            Membership::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'organisation_id' => $org->id,
                    'term_id' => $term->id,
                ],
                [
                    'division_id' => $division?->id,
                    'jobdesc_id' => $jobdesc->id,
                    'status' => 'active',
                ]
            );
        }
    }
}