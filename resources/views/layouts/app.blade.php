<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Himpun')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    // Pending requests: everything for the HOD, only the ones addressed to you for a koordinator.
    $pendingReqCount = collect(session('division_requests', \App\Support\DummyData::divisionRequests()))
        ->where('status', 'Menunggu')
        ->when($me['role'] === 'koor', fn ($c) => $c->where('ke_koor', $me['name']))
        ->count();

    // [label, icon, href, count, roles allowed to see it]
    $nav = collect([
        ['Ringkasan', 'home', route('dashboard'), null, ['hod', 'pj']],
        ['Proker', 'flag', route('proker.index'), null, ['hod', 'pj']],
        ['Proker saya', 'flag', route('proker.index'), null, ['koor', 'anggota']],
        ['Anggota', 'users', '#', null, ['hod']],
        ['Divisi & jobdesc', 'grid', '#', null, ['hod']],
        ['Request divisi', 'megaphone', route('request.index'), $pendingReqCount ?: null, ['hod', 'koor']],
        ['Persetujuan', 'check', '#', 5, ['hod']],
        ['Anggaran', 'wallet', '#', null, ['hod']],
        ['Oprec', 'megaphone', '#', null, ['hod']],
        ['Pulse', 'sparkle', '#', null, ['hod']],
        ['Dokumen', 'file', '#', null, ['hod']],
        ['Pengaturan', 'sliders', '#', null, ['hod']],
    ])->filter(fn ($item) => in_array($me['role'], $item[4], true))->values()->all();
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
                {{-- Demo identity (stands in for login): pick a role, then who you are in it. --}}
                <form method="POST" action="{{ route('role.switch') }}" x-data="roleSwitch(@js($roleOptions), @js($me['role']), @js($me['name']))" class="flex items-center gap-2">
                    @csrf
                    <span class="hidden text-[13px] text-muted xl:inline">Tampilkan sebagai</span>
                    <select name="role" x-model="role" @change="pickRole($el.form)" aria-label="Peran" class="h-8 rounded-lg bg-white/70 px-2 text-[13px] ring-1 ring-line">
                        @foreach (\App\Support\DummyData::ROLES as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <select name="person" x-model="person" @change="$el.form.submit()" aria-label="Pengguna" class="h-8 w-28 rounded-lg bg-white/70 px-2 text-[13px] ring-1 ring-line sm:w-40">
                        <template x-for="name in options[role]" :key="name">
                            <option :value="name" x-text="name"></option>
                        </template>
                    </select>
                </form>
                <button type="button" class="text-muted hover:text-ink" aria-label="Notifikasi"><x-icon name="bell" /></button>
                <x-avatar :name="$me['name']" />
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
