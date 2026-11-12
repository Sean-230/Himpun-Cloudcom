<?php

use App\Support\DummyData;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::get('/dashboard', fn () => view('dashboard', [
    'members' => DummyData::members(),
    'prokers' => DummyData::prokers(),
]))->name('dashboard');

Route::get('/proker/baru', fn () => view('proker.create', [
    'members' => DummyData::members(),
    'divisions' => DummyData::divisionDefs(),
]))->name('proker.create');
