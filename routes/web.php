<?php

use App\Models\Division;
use App\Models\Jobdesc;
use App\Models\Membership;
use App\Models\Organisation;
use App\Models\Term;
use App\Support\Access;
use App\Support\DummyData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
Route::redirect('/', '/dashboard');

// Demo identity switcher (stands in for login): pick a role, then who you are in it.
Route::post('/peran', function (Request $request) {
    $role = $request->validate(['role' => ['required', Rule::in(array_keys(DummyData::ROLES))]])['role'];
    $person = $request->validate(['person' => ['required', Rule::in(DummyData::peopleFor($role))]])['person'];

    session(['demo_role' => $role, 'demo_user' => $person]);

    // The page being viewed may be off limits to the new role, so land on its own home.
    return redirect()->route(Access::home(Access::me()));
})->name('role.switch');

// HOD sees the full summary, a PJ a short one, koordinator and anggota go straight to their prokers.
Route::get('/dashboard', function () {
    $me = Access::me();

    return match ($me['role']) {
        'hod' => view('dashboard', ['members' => DummyData::members(), 'prokers' => DummyData::prokers()]),
        'pj' => view('dashboard-pj', ['prokers' => Access::listFor($me)]),
        default => redirect()->route('proker.index'),
    };
})->name('dashboard');

Route::get('/proker', function () {
    $me = Access::me();

    return view('proker.index', ['prokers' => Access::listFor($me), 'me' => $me]);
})->name('proker.index');

require __DIR__.'/proker_write.php';
require __DIR__.'/ai.php';

Route::get('/proker/{id}', function (int $id) {
    $proker = DummyData::proker($id) ?? abort(404);
    abort_unless(Access::canSee(Access::me(), $proker), 403);

    // Only the HOD changes jobdesc and meeting schedules; everyone else reads.
    return view('proker.show', ['proker' => $proker, 'canEdit' => Access::is('hod')]);
})->whereNumber('id')->name('proker.show');

// --- FITUR REQUEST ANTAR-KOORDINATOR ---
// HOD reads every request; a koordinator reads, sends and answers their own; PJ and anggota have no access.
Route::get('/request-divisi', function () {
    $me = Access::me();
    $all = session('division_requests', DummyData::divisionRequests());
    $requests = $me['role'] === 'hod'
        ? $all
        : array_values(array_filter($all, fn ($r) => in_array($me['name'], [$r['dari_koor'], $r['ke_koor']], true)));

    // Prokers (not finished) and the divisions of each that this koordinator leads: the only ones they may send from.
    $coordinated = [];
    foreach (DummyData::prokers() as $p) {
        $divisions = $p['status'] === 'Selesai' ? [] : Access::divisionsCoordinated($p, $me['name']);
        if ($divisions) {
            $coordinated[$p['nama']] = $divisions;
        }
    }

    return view('request.index', [
        'requests' => $requests,
        'prokers' => DummyData::prokers(),
        'divisions' => DummyData::divisionDefs(),
        'isKoor' => $me['role'] === 'koor',
        'canCreate' => $me['role'] === 'koor' && $coordinated !== [],
        'coordinated' => $coordinated,
        'me' => $me,
    ]);
})->middleware('role:hod,koor')->name('request.index');

Route::post('/request-divisi', function (Request $request) {
    $me = Access::me();
    $validated = $request->validate([
        'proker' => 'required|string',
        'dari_divisi' => 'required|string',
        'ke_divisi' => 'required|string|different:dari_divisi',
        'judul' => 'required|string|max:150',
        'tenggat' => 'required|date',
        'catatan' => 'nullable|string|max:500',
    ]);

    // A koordinator can only send on behalf of a division they lead in that proker.
    $prokerData = collect(DummyData::prokers())->firstWhere('nama', $validated['proker']);
    abort_unless(
        $prokerData && in_array($validated['dari_divisi'], Access::divisionsCoordinated($prokerData, $me['name']), true),
        403,
    );

    // Cari nama koordinator tujuan otomatis berdasarkan proker & divisi yang dipilih
    $keDiv = collect($prokerData['divisions'])->firstWhere('name', $validated['ke_divisi']);

    $requests = session('division_requests', DummyData::divisionRequests());

    array_unshift($requests, [
        'id' => now()->timestamp,
        'proker' => $validated['proker'],
        'dari_divisi' => $validated['dari_divisi'],
        'dari_koor' => $me['name'],
        'ke_divisi' => $validated['ke_divisi'],
        'ke_koor' => $keDiv['koor'][0] ?? 'Koordinator '.$validated['ke_divisi'],
        'judul' => $validated['judul'],
        'catatan' => $validated['catatan'] ?? '-',
        'tenggat' => $validated['tenggat'],
        'status' => 'Menunggu',
        'waktu' => 'Baru saja',
    ]);

    session(['division_requests' => $requests]);

    return redirect()->route('request.index')->with('success', 'Permintaan ke divisi '.$validated['ke_divisi'].' berhasil dikirim.');
})->middleware('role:koor')->name('request.store');

Route::patch('/request-divisi/{id}/status', function (Request $request, int $id) {
    $me = Access::me();
    $newStatus = $request->validate(['status' => 'required|in:Diproses,Selesai,Ditolak'])['status'];
    $requests = session('division_requests', DummyData::divisionRequests());

    foreach ($requests as &$item) {
        if ($item['id'] === $id) {
            // Only the koordinator the request is addressed to may answer it.
            abort_unless($item['ke_koor'] === $me['name'], 403);
            $item['status'] = $newStatus;
            break;
        }
    }
    unset($item);

    session(['division_requests' => $requests]);

    return redirect()->route('request.index')->with('success', 'Status permintaan diperbarui menjadi '.$newStatus.'.');
})->middleware('role:koor')->name('request.status');

// --- ADMIN CONSOLE & ORGANISATION MASTER DATA (BB) ---
Route::middleware('role:hod')->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', fn () => view('admin.adminDasboard'))->name('dashboard');
    });

    Route::get('/anggota', function (Request $request) {
        $search = trim((string) $request->query('q', ''));
        $divisionFilter = (string) $request->query('division', '');

        $term = Term::where('is_active', true)->first();
        $divisions = Division::orderBy('name')->get();

        $memberships = Membership::with(['user', 'division', 'jobdesc'])
            ->when($term, fn ($q) => $q->where('term_id', $term->id))
            ->when($search !== '', fn ($q) => $q->whereHas('user', fn ($u) => $u
                ->where('name', 'like', "%{$search}%")
                ->orWhere('nim', 'like', "%{$search}%")))
            ->when($divisionFilter === 'hod', fn ($q) => $q->whereNull('division_id'))
            ->when($divisionFilter !== '' && $divisionFilter !== 'hod', fn ($q) => $q->where('division_id', $divisionFilter))
            ->get()
            ->sortBy(fn ($m) => [$m->division_id ?? 0, $m->user->name])
            ->values();

        return view('members.MemberView', compact('memberships', 'divisions', 'term', 'search', 'divisionFilter'));
    })->name('members.index');

    Route::get('/divisi', function () {
        $organisation = Organisation::first();
        $divisions = Division::with('jobdescs')->withCount('memberships')->orderBy('name')->get();
        $hodJobdescs = Jobdesc::whereNull('division_id')->get();

        return view('divisions.DivisionsView', compact('organisation', 'divisions', 'hodJobdescs'));
    })->name('divisions.index');

    Route::post('/divisi', function (Request $request) {
        $org = Organisation::firstOrFail();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('divisions', 'name')->where('organisation_id', $org->id)],
        ]);

        Division::create([
            'organisation_id' => $org->id,
            'name' => $validated['name'],
        ]);

        return back()->with('success', 'Divisi berhasil ditambahkan.');
    })->name('divisions.store');

    Route::patch('/divisi/{division}', function (Request $request, Division $division) {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('divisions', 'name')->where('organisation_id', $division->organisation_id)->ignore($division->id)],
        ]);

        $division->update($validated);

        return back()->with('success', 'Divisi berhasil diperbarui.');
    })->name('divisions.update');

    Route::post('/jobdesc', function (Request $request) {
        $org = Organisation::firstOrFail();
        $validated = $request->validate([
            'division_id' => ['nullable', 'exists:divisions,id'],
            'title' => ['required', 'string', 'max:100'],
            'level' => ['required', Rule::in(['dosen', 'hod', 'koordinator', 'staff'])],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        Jobdesc::create([
            'organisation_id' => $org->id,
            'division_id' => $validated['division_id'] ?: null,
            'title' => $validated['title'],
            'level' => $validated['level'],
            'description' => $validated['description'] ?? null,
        ]);

        return back()->with('success', 'Jobdesc berhasil ditambahkan.');
    })->name('jobdescs.store');

    Route::patch('/jobdesc/{jobdesc}', function (Request $request, Jobdesc $jobdesc) {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'level' => ['required', Rule::in(['dosen', 'hod', 'koordinator', 'staff'])],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $jobdesc->update($validated);

        return back()->with('success', 'Jobdesc berhasil diperbarui.');
    })->name('jobdescs.update');
});

// --- AUTHENTICATION ---
Route::get('/login', fn () => view('auth.login'))->middleware('guest')->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);

    if (! auth::attempt($credentials, $request->boolean('remember'))) {
        return back()->withErrors([
            'email' => 'Email atau kata sandi tidak cocok.',
        ])->onlyInput('email');
    }

    $request->session()->regenerate();

    return redirect()->intended(route(Access::home(Access::me())));
})->middleware('guest')->name('login.store');

Route::post('/logout', function (Request $request) {
    auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');