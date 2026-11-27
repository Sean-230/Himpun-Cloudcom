<?php

// Owned by Sean: Proker creation screen and server-side POST /proker write path.

use App\Http\Controllers\ProkerWriteController;
use Illuminate\Support\Facades\Route;

Route::middleware('role:hod')->group(function () {
    Route::get('/proker/baru', [ProkerWriteController::class, 'create'])->name('proker.create');
    Route::post('/proker', [ProkerWriteController::class, 'store'])->name('proker.store');
});
