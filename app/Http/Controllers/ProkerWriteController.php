<?php

namespace App\Http\Controllers;

use App\Enums\ProkerStatus;
use App\Http\Requests\StoreProkerRequest;
use App\Models\Membership;
use App\Models\Organisation;
use App\Models\Proker;
use App\Models\ProkerAssignment;
use App\Models\ProkerDivision;
use App\Models\ProkerDivisionJobdesc;
use App\Models\Term;
use App\Support\DummyData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProkerWriteController extends Controller
{
    public function create(): View
    {
        $organisation = Organisation::first();
        $term = $organisation
            ? Term::where('organisation_id', $organisation->id)->where('is_active', true)->first()
                ?? Term::where('organisation_id', $organisation->id)->latest('id')->first()
            : null;

        $dbMembers = Membership::with(['user', 'division', 'jobdesc'])
            ->withCount([
                'prokerAssignments as open_roles_count' => fn ($q) => $q->whereHas(
                    'proker',
                    fn ($p) => $p->where('status', '!=', ProkerStatus::Selesai->value)
                ),
            ])
            ->when($organisation, fn ($q) => $q->where('organisation_id', $organisation->id))
            ->when($term, fn ($q) => $q->where('term_id', $term->id))
            ->where('status', 'active')
            ->get();

        $members = $dbMembers->isNotEmpty()
            ? $dbMembers->map(function (Membership $m): array {
                $isHod = $m->jobdesc?->level === 'hod' || $m->division_id === null;
                $jabatan = $m->jobdesc?->title ?? 'Staff';

                return [
                    'id' => $m->id,
                    'name' => $m->user->name,
                    'nim' => $m->user->nim,
                    'divisi' => $isHod ? "HOD · {$jabatan}" : ($m->division?->name ?? 'Umum'),
                    'jabatan' => $jabatan,
                    'hod' => $isHod,
                    'roles' => (int) $m->open_roles_count,
                ];
            })->sortBy(fn ($m) => [$m['hod'] ? 0 : 1, $m['divisi'], $m['name']])->values()->all()
            : DummyData::members();

        $previousProkers = $organisation
            ? Proker::where('organisation_id', $organisation->id)
                ->orderByDesc('starts_on')
                ->get(['id', 'name'])
                ->map(fn (Proker $p) => ['id' => $p->id, 'name' => $p->name])
                ->all()
            : [];

        return view('proker.create', [
            'members' => $members,
            'divisions' => DummyData::divisionDefs(),
            'previousProkers' => $previousProkers,
            'organisation' => $organisation,
            'term' => $term,
        ]);
    }

    public function store(StoreProkerRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();

        $organisation = Organisation::firstOrFail();
        $term = Term::where('organisation_id', $organisation->id)->where('is_active', true)->first()
            ?? Term::where('organisation_id', $organisation->id)->latest('id')->firstOrFail();

        $proker = DB::transaction(function () use ($validated, $organisation, $term): Proker {
            $hasKetupel = ! empty($validated['ketupel_membership_id']);

            $proker = Proker::create([
                'organisation_id' => $organisation->id,
                'term_id' => $term->id,
                'previous_proker_id' => $validated['type'] === 'repeat' ? ($validated['previous_proker_id'] ?? null) : null,
                'name' => trim($validated['name']),
                'type' => $validated['type'],
                'status' => $hasKetupel ? ProkerStatus::PembentukanPanitia : ProkerStatus::Usulan,
                'starts_on' => $validated['hari_h'],
                'location' => filled($validated['location'] ?? null) ? trim((string) $validated['location']) : null,
            ]);

            $ketupelAssignment = null;
            if ($hasKetupel) {
                $ketupelAssignment = ProkerAssignment::create([
                    'proker_id' => $proker->id,
                    'membership_id' => (int) $validated['ketupel_membership_id'],
                    'role' => ProkerAssignment::ROLE_KETUPEL,
                ]);
            }

            $pjAssignment = null;
            if (! empty($validated['pj_membership_id'])) {
                $pjAssignment = ProkerAssignment::create([
                    'proker_id' => $proker->id,
                    'membership_id' => (int) $validated['pj_membership_id'],
                    'role' => ProkerAssignment::ROLE_PJ,
                    'reports_to_id' => $ketupelAssignment?->id,
                ]);
            }

            foreach ($validated['divisions'] as $divData) {
                $prokerDivision = ProkerDivision::create([
                    'proker_id' => $proker->id,
                    'division_id' => $divData['division_id'] ?? null,
                    'name' => trim((string) $divData['name']),
                ]);

                $firstKoorAssignment = null;
                foreach ($divData['koor'] ?? [] as $koorMembershipId) {
                    $koorAssignment = ProkerAssignment::create([
                        'proker_id' => $proker->id,
                        'membership_id' => (int) $koorMembershipId,
                        'proker_division_id' => $prokerDivision->id,
                        'role' => ProkerAssignment::ROLE_KOORDINATOR,
                        'reports_to_id' => $pjAssignment?->id ?? $ketupelAssignment?->id,
                    ]);
                    $firstKoorAssignment ??= $koorAssignment;
                }

                foreach ($divData['anggota'] ?? [] as $memberMembershipId) {
                    ProkerAssignment::create([
                        'proker_id' => $proker->id,
                        'membership_id' => (int) $memberMembershipId,
                        'proker_division_id' => $prokerDivision->id,
                        'role' => ProkerAssignment::ROLE_MEMBER,
                        'reports_to_id' => $firstKoorAssignment?->id ?? $pjAssignment?->id ?? $ketupelAssignment?->id,
                    ]);
                }

                foreach ($divData['jobdesc'] ?? [] as $jobData) {
                    ProkerDivisionJobdesc::create([
                        'proker_division_id' => $prokerDivision->id,
                        'title' => trim((string) $jobData['title']),
                        'days_before' => (int) $jobData['h'],
                        'done' => false,
                    ]);
                }
            }

            return $proker;
        });

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $proker->id,
                'name' => $proker->name,
                'status' => $proker->status->value,
                'redirect' => route('proker.index'),
            ], 201);
        }

        return redirect()
            ->route('proker.index')
            ->with('success', "Proker \"{$proker->name}\" berhasil disimpan.");
    }
}
