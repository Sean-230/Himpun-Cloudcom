<?php

use App\Support\DummyData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::get('/dashboard', fn () => view('dashboard', [
    'members' => DummyData::members(),
    'prokers' => DummyData::prokers(),
]))->name('dashboard');

Route::get('/proker', fn () => view('proker.index', [
    'prokers' => array_values(array_filter(DummyData::prokers(), fn ($p) => $p['status'] === 'Berjalan')),
]))->name('proker.index');

Route::get('/proker/baru', fn () => view('proker.create', [
    'members' => DummyData::members(),
    'divisions' => DummyData::divisionDefs(),
]))->name('proker.create');

Route::get('/proker/{id}', fn (int $id) => view('proker.show', [
    'proker' => DummyData::proker($id) ?? abort(404),
]))->whereNumber('id')->name('proker.show');

// --- FITUR REQUEST ANTAR-KOORDINATOR ---
Route::get('/request-divisi', function () {
    $requests = session('division_requests', DummyData::divisionRequests());

    return view('request.index', [
        'requests' => $requests,
        'prokers' => DummyData::prokers(),
        'divisions' => DummyData::divisionDefs(),
    ]);
})->name('request.index');

Route::post('/request-divisi', function (Request $request) {
    $validated = $request->validate([
        'proker' => 'required|string',
        'dari_divisi' => 'required|string',
        'ke_divisi' => 'required|string|different:dari_divisi',
        'judul' => 'required|string|max:150',
        'tenggat' => 'required|date',
        'catatan' => 'nullable|string|max:500',
    ]);

    // Cari nama koordinator otomatis berdasarkan proker & divisi yang dipilih
    $prokerData = collect(DummyData::prokers())->firstWhere('nama', $validated['proker']);
    $dariDiv = collect($prokerData['divisions'] ?? [])->firstWhere('name', $validated['dari_divisi']);
    $keDiv = collect($prokerData['divisions'] ?? [])->firstWhere('name', $validated['ke_divisi']);

    $requests = session('division_requests', DummyData::divisionRequests());

    array_unshift($requests, [
        'id' => now()->timestamp,
        'proker' => $validated['proker'],
        'dari_divisi' => $validated['dari_divisi'],
        'dari_koor' => $dariDiv['koor'][0] ?? 'Koordinator '.$validated['dari_divisi'],
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
})->name('request.store');

Route::patch('/request-divisi/{id}/status', function (Request $request, int $id) {
    $requests = session('division_requests', DummyData::divisionRequests());
    $newStatus = $request->input('status');

    foreach ($requests as &$item) {
        if ($item['id'] === $id) {
            $item['status'] = $newStatus;
            break;
        }
    }

    session(['division_requests' => $requests]);

    return redirect()->route('request.index')->with('success', 'Status permintaan diperbarui menjadi '.$newStatus.'.');
})->name('request.status');

// --- ADMIN CONSOLE (dari branch bb) ---
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return view('admin.adminDasboard');
    })->name('dashboard');
});
