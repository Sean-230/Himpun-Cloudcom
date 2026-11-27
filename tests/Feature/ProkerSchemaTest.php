<?php

namespace Tests\Feature;

use App\Enums\ProkerStatus;
use App\Models\Division;
use App\Models\Jobdesc;
use App\Models\Membership;
use App\Models\Organisation;
use App\Models\Proker;
use App\Models\ProkerAssignment;
use App\Models\ProkerDivision;
use App\Models\ProkerDivisionJobdesc;
use App\Models\ProkerMeeting;
use App\Models\Term;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProkerSchemaTest extends TestCase
{
    use RefreshDatabase;

    private function seedBaseContext(): array
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

        $division = Division::create([
            'organisation_id' => $org->id,
            'name' => 'Tech',
        ]);

        $jobdesc = Jobdesc::create([
            'organisation_id' => $org->id,
            'division_id' => $division->id,
            'title' => 'Staff',
            'level' => 'staff',
        ]);

        $makeMembership = function (string $name, string $email) use ($org, $term, $division, $jobdesc): Membership {
            $user = User::factory()->create(['name' => $name, 'email' => $email]);

            return Membership::create([
                'user_id' => $user->id,
                'organisation_id' => $org->id,
                'term_id' => $term->id,
                'division_id' => $division->id,
                'jobdesc_id' => $jobdesc->id,
                'status' => 'active',
            ]);
        };

        return [$org, $term, $division, $makeMembership];
    }

    public function test_proker_models_relationships_and_enum_casts_work(): void
    {
        [$org, $term, $standingDivision, $makeMembership] = $this->seedBaseContext();

        $ketupel = $makeMembership('Rafael Santoso', 'rafael@student.ciputra.ac.id');
        $koor = $makeMembership('Andreas Gunawan', 'andreas@student.ciputra.ac.id');
        $member = $makeMembership('Timothy Lee', 'timothy@student.ciputra.ac.id');

        $past = Proker::create([
            'organisation_id' => $org->id,
            'term_id' => $term->id,
            'name' => 'Techfest UC 2025',
            'type' => 'new',
            'status' => ProkerStatus::Selesai,
            'starts_on' => '2025-12-10',
            'location' => 'Auditorium UC',
        ]);

        $proker = Proker::create([
            'organisation_id' => $org->id,
            'term_id' => $term->id,
            'previous_proker_id' => $past->id,
            'name' => 'Techfest UC 2026',
            'type' => 'repeat',
            'starts_on' => '2026-12-12',
            'location' => 'Auditorium UC',
        ]);

        $this->assertSame(ProkerStatus::Usulan, $proker->fresh()->status);
        $this->assertSame('Auditorium UC', $proker->fresh()->location);
        $this->assertTrue($proker->previousProker->is($past));

        $prokerDiv = ProkerDivision::create([
            'proker_id' => $proker->id,
            'division_id' => $standingDivision->id,
            'name' => 'Tech',
        ]);

        $ketupelAssign = ProkerAssignment::create([
            'proker_id' => $proker->id,
            'membership_id' => $ketupel->id,
            'role' => ProkerAssignment::ROLE_KETUPEL,
        ]);

        $koorAssign = ProkerAssignment::create([
            'proker_id' => $proker->id,
            'membership_id' => $koor->id,
            'proker_division_id' => $prokerDiv->id,
            'role' => ProkerAssignment::ROLE_KOORDINATOR,
            'reports_to_id' => $ketupelAssign->id,
        ]);

        $memberAssign = ProkerAssignment::create([
            'proker_id' => $proker->id,
            'membership_id' => $member->id,
            'proker_division_id' => $prokerDiv->id,
            'role' => ProkerAssignment::ROLE_MEMBER,
            'reports_to_id' => $koorAssign->id,
        ]);

        $job = ProkerDivisionJobdesc::create([
            'proker_division_id' => $prokerDiv->id,
            'title' => 'Landing page dan registrasi',
            'days_before' => 28,
        ]);

        $meeting = ProkerMeeting::create([
            'proker_id' => $proker->id,
            'scope' => ProkerMeeting::SCOPE_DIVISION,
            'proker_division_id' => $prokerDiv->id,
            'weekday' => 4,
            'starts_at' => '19:00:00',
            'place' => 'Zoom',
        ]);

        $this->assertCount(1, $proker->divisions);
        $this->assertCount(3, $proker->assignments);
        $this->assertCount(1, $proker->meetings);
        $this->assertCount(1, $proker->jobdescs);
        $this->assertFalse($job->fresh()->done);
        $this->assertSame(28, $job->fresh()->days_before);
        $this->assertSame(4, $meeting->fresh()->weekday);
        $this->assertTrue($memberAssign->reportsTo->is($koorAssign));
        $this->assertTrue($koorAssign->reportsTo->is($ketupelAssign));

        // Deleting a proker cascades to divisions, assignments, jobdescs, and meetings.
        $proker->delete();
        $this->assertDatabaseCount('proker_divisions', 0);
        $this->assertDatabaseCount('proker_assignments', 0);
        $this->assertDatabaseCount('proker_division_jobdescs', 0);
        $this->assertDatabaseCount('proker_meetings', 0);
    }

    public function test_one_person_cannot_hold_two_roles_in_the_same_proker(): void
    {
        [$org, $term, , $makeMembership] = $this->seedBaseContext();
        $person = $makeMembership('Michelle Wijaya', 'michelle@student.ciputra.ac.id');

        $proker = Proker::create([
            'organisation_id' => $org->id,
            'term_id' => $term->id,
            'name' => 'Techfest UC 2026',
            'type' => 'new',
            'status' => ProkerStatus::Berjalan,
        ]);

        ProkerAssignment::create([
            'proker_id' => $proker->id,
            'membership_id' => $person->id,
            'role' => ProkerAssignment::ROLE_KETUPEL,
        ]);

        $this->expectException(QueryException::class);

        ProkerAssignment::create([
            'proker_id' => $proker->id,
            'membership_id' => $person->id,
            'role' => ProkerAssignment::ROLE_PJ,
        ]);
    }

    public function test_one_person_can_hold_roles_across_multiple_prokers(): void
    {
        [$org, $term, , $makeMembership] = $this->seedBaseContext();
        $person = $makeMembership('Andreas Gunawan', 'andreas@student.ciputra.ac.id');

        $p1 = Proker::create([
            'organisation_id' => $org->id,
            'term_id' => $term->id,
            'name' => 'Techfest UC 2026',
            'type' => 'repeat',
            'status' => ProkerStatus::Berjalan,
        ]);

        $p2 = Proker::create([
            'organisation_id' => $org->id,
            'term_id' => $term->id,
            'name' => 'Webinar Series',
            'type' => 'new',
            'status' => ProkerStatus::Berjalan,
        ]);

        ProkerAssignment::create([
            'proker_id' => $p1->id,
            'membership_id' => $person->id,
            'role' => ProkerAssignment::ROLE_KOORDINATOR,
        ]);

        ProkerAssignment::create([
            'proker_id' => $p2->id,
            'membership_id' => $person->id,
            'role' => ProkerAssignment::ROLE_MEMBER,
        ]);

        $this->assertDatabaseCount('proker_assignments', 2);
    }

    public function test_proker_cannot_have_duplicate_division_names(): void
    {
        [$org, $term] = $this->seedBaseContext();

        $proker = Proker::create([
            'organisation_id' => $org->id,
            'term_id' => $term->id,
            'name' => 'National Hackathon UC',
            'type' => 'new',
        ]);

        ProkerDivision::create(['proker_id' => $proker->id, 'name' => 'Tech']);

        $this->expectException(QueryException::class);

        ProkerDivision::create(['proker_id' => $proker->id, 'name' => 'Tech']);
    }

    public function test_proker_status_enum_helpers_match_ui_contract(): void
    {
        $this->assertSame(
            ['Usulan', 'Pembentukan panitia', 'Berjalan', 'LPJ', 'Selesai'],
            ProkerStatus::values()
        );
        $this->assertSame('usulan', ProkerStatus::Usulan->group());
        $this->assertSame('berjalan', ProkerStatus::PembentukanPanitia->group());
        $this->assertSame('berjalan', ProkerStatus::Berjalan->group());
        $this->assertSame('berjalan', ProkerStatus::Lpj->group());
        $this->assertSame('selesai', ProkerStatus::Selesai->group());
        $this->assertTrue(ProkerStatus::Berjalan->isOpen());
        $this->assertFalse(ProkerStatus::Selesai->isOpen());
        $this->assertNotEmpty(ProkerStatus::Berjalan->pillClass());
    }

    public function test_dosen_pembina_term_divisions_pre_registration_and_soft_delete_work(): void
    {
        [$org, $term2526] = $this->seedBaseContext();

        $dosen = User::factory()->create([
            'name' => 'Dr. Ir. Budi Santoso',
            'email' => 'budi.santoso@ciputra.ac.id',
        ]);
        $org->update(['advisor_user_id' => $dosen->id]);

        $this->assertTrue($dosen->isAdvisor());
        $this->assertTrue($org->fresh()->advisor->is($dosen));

        // Second period 26/27 can have its own division with the same name "Tech" without colliding with 25/26.
        $term2627 = Term::create([
            'organisation_id' => $org->id,
            'name' => '2026/2027',
            'starts_on' => '2026-08-01',
            'ends_on' => '2027-07-31',
            'is_active' => false,
        ]);

        $div2526 = Division::where('name', 'Tech')->firstOrFail();
        $div2526->update(['term_id' => $term2526->id]);

        $div2627 = Division::create([
            'organisation_id' => $org->id,
            'term_id' => $term2627->id,
            'name' => 'Tech',
        ]);

        $this->assertNotSame($div2526->id, $div2627->id);
        $this->assertCount(1, $term2627->divisions);
        $this->assertCount(6, Jobdesc::DEFAULT_HOD_TITLES);

        // Pre-registered student created by Dosen Pembina (no password yet).
        $student = User::create([
            'name' => 'Mahasiswa Baru',
            'nim' => '0706012610001',
            'email' => 'maba@student.ciputra.ac.id',
            'password' => null,
            'activated_at' => null,
        ]);

        $this->assertFalse($student->isActivated());

        // Student activates account via /register.
        $student->update([
            'password' => 'rahasia123',
            'activated_at' => now(),
        ]);
        $this->assertTrue($student->fresh()->isActivated());

        // Dosen Pembina resets password (back to unactivated).
        $student->update([
            'password' => null,
            'activated_at' => null,
        ]);
        $this->assertFalse($student->fresh()->isActivated());

        // Smart Delete (soft delete) hides user from normal queries while keeping historical records intact.
        $membership = Membership::create([
            'user_id' => $student->id,
            'organisation_id' => $org->id,
            'term_id' => $term2627->id,
            'division_id' => $div2627->id,
            'status' => 'active',
        ]);

        $student->delete();

        $this->assertSoftDeleted('users', ['id' => $student->id]);
        $this->assertDatabaseHas('memberships', ['id' => $membership->id]);
    }
}
