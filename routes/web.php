<?php

use App\Support\Access;
use App\Support\DummyData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

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

Route::get('/proker/baru', fn () => view('proker.create', [
    'members' => DummyData::members(),
    'divisions' => DummyData::divisionDefs(),
]))->middleware('role:hod')->name('proker.create');

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

// --- ADMIN CONSOLE (dari branch bb) ---
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return view('admin.adminDasboard');
    })->name('dashboard');
});
