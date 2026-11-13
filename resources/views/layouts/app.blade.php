<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Himpun')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
$pendingReqCount = collect(session('division_requests', \App\Support\DummyData::divisionRequests()))
        ->where('status', 'Menunggu')
        ->count();

    $nav = [
        ['Ringkasan', 'home', route('dashboard')],
        ['Proker', 'flag', route('proker.index')],
        ['Panitia saya', 'briefcase', '#'],
        ['Anggota', 'users', '#'],
        ['Divisi & jobdesc', 'grid', '#'],
        ['Request divisi', 'megaphone', route('request.index'), $pendingReqCount ?: null],
        ['Persetujuan', 'check', '#', 5],
        ['Anggaran', 'wallet', '#'],
        ['Oprec', 'megaphone', '#'],
        ['Pulse', 'sparkle', '#'],
        ['Dokumen', 'file', '#'],
        ['Pengaturan', 'sliders', '#'],
    ];
@endphp
<body class="bg-frame font-sans text-sm text-ink antialiased">
<div class="flex h-dvh">
    {{-- Tinted frame: sidebar and top bar share one ground with no rule between them. --}}
    <aside class="hidden w-[232px] shrink-0 flex-col gap-4 px-3 py-4 lg:flex">
        <div class="flex items-center gap-2 px-2">
            <svg class="size-6" viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="4" width="4" height="16" rx="1" fill="#0a1931"/><rect x="15" y="4" width="4" height="16" rx="1" fill="#0a1931"/><rect x="9" y="10.5" width="6" height="3" fill="#0a1931"/></svg>
            <span class="text-base font-semibold">Himpun</span>
        </div>
        <div class="rounded-[10px] px-2 py-1.5 text-[13px] text-muted">SU IMT · 2025/2026</div>
        <nav class="flex flex-col gap-0.5">
            @foreach ($nav as $item)
                @php [$label, $icon, $href] = $item; $count = $item[3] ?? null; @endphp
                <a href="{{ $href }}" @class([
                    'flex h-8 items-center gap-2.5 rounded-lg px-2 text-muted hover:bg-black/5 hover:text-ink',
                    'bg-black/5 font-medium text-ink' => request()->url() === $href || ($href !== '#' && str_starts_with(request()->url(), $href.'/')),
                ])>
                    <x-icon :name="$icon" />
                    <span>{{ $label }}</span>
                    @isset($count)
                        <span class="ml-auto rounded-full bg-black/5 px-1.5 text-[13px] tabular-nums">{{ $count }}</span>
                    @endisset
                </a>
            @endforeach
        </nav>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col">
        <header class="flex h-12 shrink-0 items-center gap-3 px-4 lg:px-5">
            <details class="relative lg:hidden">
                <summary class="flex h-8 cursor-pointer list-none items-center gap-2 rounded-lg px-2 font-semibold hover:bg-black/5"><x-icon name="grid" />Himpun</summary>
                <nav class="absolute left-0 top-10 z-10 flex w-56 flex-col gap-0.5 rounded-xl border border-line bg-white p-2">
                    @foreach ($nav as $item)
                        <a href="{{ $item[2] }}" class="flex h-9 items-center gap-2.5 rounded-lg px-2 text-muted hover:bg-black/5 hover:text-ink"><x-icon :name="$item[1]" />{{ $item[0] }}</a>
                    @endforeach
                </nav>
            </details>
            <label class="mx-auto hidden h-8 w-full max-w-[548px] items-center gap-2 rounded-lg bg-white/70 px-3 text-muted ring-1 ring-line md:flex">
                <x-icon name="search" />
                <input type="search" placeholder="Cari proker, anggota, tugas" class="min-w-0 flex-1 bg-transparent outline-none placeholder:text-muted">
                <kbd class="text-[13px]">⌘K</kbd>
            </label>
            <div class="ml-auto flex items-center gap-3">
                <span class="hidden text-[13px] text-muted md:inline">Tampilkan sebagai</span>
                <select aria-label="Tampilkan sebagai" class="h-8 rounded-lg bg-white/70 px-2 text-[13px] ring-1 ring-line">
                    <option>HOD/PJ</option>
                    <option>Anggota</option>
                    <option>Koordinator</option>
                    <option>Ketupel</option>
                    <option>Dosen Pembina</option>
                </select>
                <button type="button" class="text-muted hover:text-ink" aria-label="Notifikasi"><x-icon name="bell" /></button>
                <x-avatar name="Michelle Wijaya" />
            </div>
        </header>

        {{-- The white sheet, inset 8px from the window's right and bottom edges. --}}
        <main class="min-h-0 flex-1 overflow-y-auto border-line bg-white p-4 lg:mb-2 lg:mr-2 lg:rounded-2xl lg:border lg:p-6">
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
