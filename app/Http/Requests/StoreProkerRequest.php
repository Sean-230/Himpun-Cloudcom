<?php

namespace App\Http\Requests;

use App\Models\Membership;
use App\Models\Organisation;
use App\Models\Term;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreProkerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:new,repeat'],
            'previous_proker_id' => ['nullable', 'required_if:type,repeat', 'integer', 'exists:prokers,id'],
            'hari_h' => ['required', 'date', 'after_or_equal:today'],
            'location' => ['nullable', 'string', 'max:120'],
            'ketupel_membership_id' => ['nullable', 'integer', 'exists:memberships,id'],
            'pj_membership_id' => ['nullable', 'integer', 'exists:memberships,id'],
            'divisions' => ['required', 'array', 'min:1'],
            'divisions.*.name' => ['required', 'string', 'max:40'],
            'divisions.*.division_id' => ['nullable', 'integer', 'exists:divisions,id'],
            'divisions.*.koor' => ['nullable', 'array'],
            'divisions.*.koor.*' => ['integer', 'exists:memberships,id'],
            'divisions.*.anggota' => ['nullable', 'array'],
            'divisions.*.anggota.*' => ['integer', 'exists:memberships,id'],
            'divisions.*.jobdesc' => ['nullable', 'array'],
            'divisions.*.jobdesc.*.title' => ['required', 'string', 'max:120'],
            'divisions.*.jobdesc.*.h' => ['required', 'integer', 'min:-60', 'max:365'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama proker wajib diisi.',
            'hari_h.required' => 'Hari-H wajib diisi.',
            'hari_h.after_or_equal' => 'Hari-H tidak boleh sebelum hari ini.',
            'previous_proker_id.required_if' => 'Pilih proker periode lalu yang diulang.',
            'divisions.required' => 'Pilih minimal satu divisi.',
            'divisions.min' => 'Pilih minimal satu divisi.',
            'divisions.*.jobdesc.*.title.required' => 'Ada jobdesc yang judulnya masih kosong. Isi atau hapus.',
            'divisions.*.jobdesc.*.h.integer' => 'Target jobdesc harus berupa angka (hari sebelum Hari-H).',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $divisions = $this->input('divisions', []);

                // 1. Prevent duplicate division names (case-insensitive) inside the same proker.
                $seenDivisions = [];
                foreach ($divisions as $div) {
                    $normalized = mb_strtolower(trim((string) ($div['name'] ?? '')));
                    if ($normalized === '') {
                        $validator->errors()->add('divisions', 'Nama divisi tidak boleh kosong.');

                        return;
                    }
                    if (isset($seenDivisions[$normalized])) {
                        $validator->errors()->add('divisions', 'Nama divisi tidak boleh duplikat dalam satu proker.');

                        return;
                    }
                    $seenDivisions[$normalized] = true;
                }

                // 2. Collect every role held by every membership_id in this proker.
                $ketupelId = $this->filled('ketupel_membership_id') ? (int) $this->input('ketupel_membership_id') : null;
                $pjId = $this->filled('pj_membership_id') ? (int) $this->input('pj_membership_id') : null;

                $rolesByMember = [];
                $addRole = function (?int $id, string $label) use (&$rolesByMember): void {
                    if ($id === null) {
                        return;
                    }
                    $rolesByMember[$id][] = $label;
                };

                $addRole($ketupelId, 'Ketupel');
                $addRole($pjId, 'PJ');

                foreach ($divisions as $div) {
                    $divName = trim((string) $div['name']);
                    foreach ($div['koor'] ?? [] as $id) {
                        $addRole((int) $id, "koordinator {$divName}");
                    }
                    foreach ($div['anggota'] ?? [] as $id) {
                        $addRole((int) $id, "anggota {$divName}");
                    }
                }

                // 3. Enforce "Ketupel != PJ" and "one person, one role per proker".
                foreach ($rolesByMember as $id => $roles) {
                    if (count($roles) < 2) {
                        continue;
                    }

                    if (in_array('Ketupel', $roles, true) && in_array('PJ', $roles, true)) {
                        $validator->errors()->add('ketupel_membership_id', 'Ketupel dan PJ tidak boleh orang yang sama.');
                    } elseif (in_array('Ketupel', $roles, true)) {
                        $validator->errors()->add('ketupel_membership_id', 'Satu orang hanya boleh memegang satu peran di satu proker.');
                    } elseif (in_array('PJ', $roles, true)) {
                        $validator->errors()->add('pj_membership_id', 'Satu orang hanya boleh memegang satu peran di satu proker.');
                    } else {
                        $validator->errors()->add('people', 'Satu orang hanya boleh memegang satu peran di satu proker.');
                    }
                }

                if ($rolesByMember === []) {
                    return;
                }

                // 4. Ensure all assigned memberships belong to the active org/term and PJ is an HOD.
                $org = Organisation::first();
                $term = $org ? Term::where('organisation_id', $org->id)->where('is_active', true)->first() : null;

                $memberships = Membership::with('jobdesc')
                    ->whereIn('id', array_keys($rolesByMember))
                    ->get()
                    ->keyBy('id');

                if ($org && $term) {
                    foreach ($memberships as $m) {
                        if ($m->organisation_id !== $org->id || $m->term_id !== $term->id) {
                            $validator->errors()->add('people', 'Anggota yang dipilih harus berasal dari periode aktif.');
                            break;
                        }
                    }
                }

                if ($pjId !== null) {
                    $pj = $memberships->get($pjId);
                    $isHod = $pj && ($pj->jobdesc?->level === 'hod' || $pj->division_id === null);
                    if (! $isHod) {
                        $validator->errors()->add('pj_membership_id', 'Penanggung jawab (PJ) harus berasal dari HOD.');
                    }
                }
            },
        ];
    }
}
