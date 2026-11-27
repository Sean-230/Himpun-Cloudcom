<?php

namespace Tests\Feature;

use App\Enums\ProkerStatus;
use App\Models\Division;
use App\Models\Jobdesc;
use App\Models\Membership;
use App\Models\Organisation;
use App\Models\Proker;
use App\Models\ProkerAssignment;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ProkerCreateTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** @return array{org: Organisation, term: Term, hodUser: User, hodMembership: Membership, staffUser: User, staffMemberships: list<Membership>} */
    private function seedOrganisationContext(): array
    {
        $org = Organisation::create([
            'name' => 'SU IMT',
            'slug' => 'su-imt',
            'type' => 'himpunan',
            'faculty' => 'School of Information Technology',
        ]);

        $term = Term::create([
            'organisation_id' => $org->id,
            'name' => '2025/2026',
            'starts_on' => '2025-08-01',
            'ends_on' => '2026-07-31',
            'is_active' => true,
        ]);

        $techDiv = Division::create([
            'organisation_id' => $org->id,
            'term_id' => $term->id,
            'name' => 'Tech',
        ]);

        $hodJob = Jobdesc::create([
            'organisation_id' => $org->id,
            'term_id' => $term->id,
            'division_id' => null,
            'title' => 'President',
            'level' => 'hod',
        ]);

        $staffJob = Jobdesc::create([
            'organisation_id' => $org->id,
            'term_id' => $term->id,
            'division_id' => $techDiv->id,
            'title' => 'Staff',
            'level' => 'staff',
        ]);

        $hodUser = User::factory()->create([
            'name' => 'Clarissa Tan',
            'email' => 'clarissa.tan@student.ciputra.ac.id',
            'activated_at' => now(),
        ]);

        $hodMembership = Membership::create([
            'user_id' => $hodUser->id,
            'organisation_id' => $org->id,
            'term_id' => $term->id,
            'division_id' => null,
            'jobdesc_id' => $hodJob->id,
            'status' => 'active',
        ]);

        $staffMemberships = [];
        $staffUser = null;
        foreach (['Rafael Santoso', 'Andreas Gunawan', 'Timothy Lee'] as $i => $name) {
            $u = User::factory()->create([
                'name' => $name,
                'email' => "staff{$i}@student.ciputra.ac.id",
                'activated_at' => now(),
            ]);
            $staffUser ??= $u;

            $staffMemberships[] = Membership::create([
                'user_id' => $u->id,
                'organisation_id' => $org->id,
                'term_id' => $term->id,
                'division_id' => $techDiv->id,
                'jobdesc_id' => $staffJob->id,
                'status' => 'active',
            ]);
        }

        return compact('org', 'term', 'hodUser', 'hodMembership', 'staffUser', 'staffMemberships');
    }

    /** @return array<string, mixed> */
    private function validPayload(array $ctx, array $override = []): array
    {
        return array_replace_recursive([
            'name' => 'Techfest UC 2026',
            'type' => 'new',
            'hari_h' => now()->addWeeks(3)->toDateString(),
            'location' => 'Auditorium UC',
            'ketupel_membership_id' => $ctx['staffMemberships'][0]->id,
            'pj_membership_id' => $ctx['hodMembership']->id,
            'divisions' => [
                [
                    'name' => 'Tech',
                    'koor' => [$ctx['staffMemberships'][1]->id],
                    'anggota' => [$ctx['staffMemberships'][2]->id],
                    'jobdesc' => [
                        ['title' => 'Landing page dan registrasi', 'h' => 28],
                        ['title' => 'Rekap data peserta', 'h' => -2],
                    ],
                ],
            ],
        ], $override);
    }

    public function test_only_hod_can_open_and_submit_proker_create(): void
    {
        $ctx = $this->seedOrganisationContext();

        $this->withoutVite()->get('/proker/baru')->assertForbidden();
        $this->postJson('/proker', $this->validPayload($ctx))->assertForbidden();

        $this->actingAs($ctx['staffUser'])->withoutVite()->get('/proker/baru')->assertForbidden();
        $this->actingAs($ctx['staffUser'])->postJson('/proker', $this->validPayload($ctx))->assertForbidden();

        $this->actingAs($ctx['hodUser'])
            ->withoutVite()
            ->get('/proker/baru')
            ->assertOk()
            ->assertSee('Buat proker')
            ->assertSee('Clarissa Tan');
    }

    public function test_hod_can_save_a_valid_proker_with_divisions_assignments_and_jobdescs(): void
    {
        $ctx = $this->seedOrganisationContext();

        $response = $this->actingAs($ctx['hodUser'])
            ->post('/proker', $this->validPayload($ctx));

        $response->assertRedirect(route('proker.index'))
            ->assertSessionHas('success');

        $proker = Proker::with(['divisions.jobdescs', 'assignments'])->firstOrFail();

        $this->assertSame('Techfest UC 2026', $proker->name);
        $this->assertSame('Auditorium UC', $proker->location);
        $this->assertSame(ProkerStatus::PembentukanPanitia, $proker->status);
        $this->assertCount(1, $proker->divisions);
        $this->assertCount(2, $proker->divisions->first()->jobdescs);
        $this->assertCount(4, $proker->assignments);

        // Verify strict reporting chain: Member -> Koordinator -> PJ -> Ketupel.
        $byRole = $proker->assignments->keyBy('role');
        $this->assertNull($byRole[ProkerAssignment::ROLE_KETUPEL]->reports_to_id);
        $this->assertSame($byRole[ProkerAssignment::ROLE_KETUPEL]->id, $byRole[ProkerAssignment::ROLE_PJ]->reports_to_id);
        $this->assertSame($byRole[ProkerAssignment::ROLE_PJ]->id, $byRole[ProkerAssignment::ROLE_KOORDINATOR]->reports_to_id);
        $this->assertSame($byRole[ProkerAssignment::ROLE_KOORDINATOR]->id, $byRole[ProkerAssignment::ROLE_MEMBER]->reports_to_id);
    }

    public function test_proker_without_ketupel_starts_as_usulan(): void
    {
        $ctx = $this->seedOrganisationContext();

        $payload = $this->validPayload($ctx, [
            'ketupel_membership_id' => null,
            'pj_membership_id' => null,
            'divisions' => [
                ['name' => 'Event', 'koor' => [], 'anggota' => [], 'jobdesc' => []],
            ],
        ]);

        $this->actingAs($ctx['hodUser'])
            ->postJson('/proker', $payload)
            ->assertCreated()
            ->assertJsonPath('status', ProkerStatus::Usulan->value);
    }

    public function test_repeat_proker_requires_previous_proker_id(): void
    {
        $ctx = $this->seedOrganisationContext();

        $this->actingAs($ctx['hodUser'])
            ->postJson('/proker', $this->validPayload($ctx, ['type' => 'repeat', 'previous_proker_id' => null]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('previous_proker_id');

        $past = Proker::create([
            'organisation_id' => $ctx['org']->id,
            'term_id' => $ctx['term']->id,
            'name' => 'Techfest UC 2025',
            'type' => 'new',
            'status' => ProkerStatus::Selesai,
        ]);

        $this->actingAs($ctx['hodUser'])
            ->postJson('/proker', $this->validPayload($ctx, ['type' => 'repeat', 'previous_proker_id' => $past->id]))
            ->assertCreated();

        $this->assertDatabaseHas('prokers', [
            'name' => 'Techfest UC 2026',
            'previous_proker_id' => $past->id,
        ]);
    }

    public function test_hari_h_uses_asia_jakarta_timezone_and_rejects_past_dates(): void
    {
        $this->assertSame('Asia/Jakarta', config('app.timezone'));

        // 2026-11-27 01:00 WIB is still 2026-11-26 18:00 UTC!
        Carbon::setTestNow(Carbon::create(2026, 11, 27, 1, 0, 0, 'Asia/Jakarta'));

        $ctx = $this->seedOrganisationContext();

        $this->actingAs($ctx['hodUser'])
            ->postJson('/proker', $this->validPayload($ctx, ['hari_h' => '2026-11-26']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('hari_h');

        $this->actingAs($ctx['hodUser'])
            ->postJson('/proker', $this->validPayload($ctx, ['hari_h' => '2026-11-27']))
            ->assertCreated();
    }

    public function test_rejects_ketupel_equal_to_pj_and_non_hod_pj(): void
    {
        $ctx = $this->seedOrganisationContext();

        // Ketupel == PJ (same HOD)
        $this->actingAs($ctx['hodUser'])
            ->postJson('/proker', $this->validPayload($ctx, [
                'ketupel_membership_id' => $ctx['hodMembership']->id,
                'pj_membership_id' => $ctx['hodMembership']->id,
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('ketupel_membership_id');

        // PJ is not HOD
        $this->actingAs($ctx['hodUser'])
            ->postJson('/proker', $this->validPayload($ctx, [
                'pj_membership_id' => $ctx['staffMemberships'][2]->id,
                'divisions' => [
                    ['name' => 'Tech', 'koor' => [], 'anggota' => [], 'jobdesc' => []],
                ],
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('pj_membership_id');
    }

    public function test_rejects_one_person_holding_two_roles_in_same_proker(): void
    {
        $ctx = $this->seedOrganisationContext();

        $duplicateId = $ctx['staffMemberships'][0]->id;

        $this->actingAs($ctx['hodUser'])
            ->postJson('/proker', $this->validPayload($ctx, [
                'ketupel_membership_id' => $duplicateId,
                'divisions' => [
                    ['name' => 'Tech', 'koor' => [$duplicateId], 'anggota' => [], 'jobdesc' => []],
                ],
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('ketupel_membership_id');
    }

    public function test_rejects_duplicate_divisions_and_invalid_jobdescs(): void
    {
        $ctx = $this->seedOrganisationContext();

        $this->actingAs($ctx['hodUser'])
            ->postJson('/proker', $this->validPayload($ctx, [
                'divisions' => [
                    ['name' => 'Tech', 'koor' => [], 'anggota' => [], 'jobdesc' => []],
                    ['name' => 'tech', 'koor' => [], 'anggota' => [], 'jobdesc' => []],
                ],
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('divisions');

        $this->actingAs($ctx['hodUser'])
            ->postJson('/proker', $this->validPayload($ctx, [
                'divisions' => [
                    [
                        'name' => 'Tech',
                        'koor' => [],
                        'anggota' => [],
                        'jobdesc' => [['title' => '', 'h' => 10]],
                    ],
                ],
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('divisions.0.jobdesc.0.title');
    }
}
