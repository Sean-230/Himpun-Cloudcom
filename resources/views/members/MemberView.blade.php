@extends('layouts.app')
@section('title', 'Anggota — Himpun')

@section('content')
<div class="flex flex-col gap-6">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-[22px] font-semibold leading-tight">Anggota</h1>
            <p class="mt-0.5 text-muted">Direktori pengurus aktif SU IMT · Periode {{ $term?->name ?? '2025/2026' }}</p>
        </div>
        <span class="rounded-full bg-black/5 px-3 py-1 text-[13px] tabular-nums">{{ $memberships->count() }} anggota aktif</span>
    </div>

    {{-- Search & Filter --}}
    <form method="GET" action="{{ route('members.index') }}" class="flex flex-wrap items-center gap-3">
        <input type="search" name="q" value="{{ $search }}" placeholder="Cari nama atau NIM..."
               class="h-9 w-full max-w-xs rounded-lg border border-line px-3 text-sm outline-none focus:border-accent">
        <select name="division" onchange="this.form.submit()" class="h-9 rounded-lg border border-line bg-white px-3 text-sm">
            <option value="">Semua divisi</option>
            <option value="hod" @selected($divisionFilter === 'hod')>HOD (Executive Board)</option>
            @foreach ($divisions as $div)
                <option value="{{ $div->id }}" @selected((string) $divisionFilter === (string) $div->id)>{{ $div->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="h-9 rounded-lg bg-accent px-4 text-[13px] font-medium text-white">Cari</button>
        @if ($search !== '' || $divisionFilter !== '')
            <a href="{{ route('members.index') }}" class="text-[13px] text-muted hover:text-ink">Reset</a>
        @endif
    </form>

    {{-- Members Table --}}
    <section class="rounded-xl border border-line p-5">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[600px] text-left">
                <thead class="text-[13px] text-muted">
                    <tr>
                        <th class="py-2 font-normal">Nama</th>
                        <th class="py-2 font-normal">NIM</th>
                        <th class="py-2 font-normal">Divisi</th>
                        <th class="py-2 font-normal">Jabatan</th>
                        <th class="py-2 font-normal">Email</th>
                        <th class="py-2 text-right font-normal">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line border-t border-line">
                    @forelse ($memberships as $m)
                        <tr>
                            <td class="py-3 pr-3 font-medium">
                                <span class="flex items-center gap-2.5">
                                    <x-avatar :name="$m->user->name" />
                                    {{ $m->user->name }}
                                </span>
                            </td>
                            <td class="py-3 pr-3 tabular-nums text-muted">{{ $m->user->nim ?? '–' }}</td>
                            <td class="py-3 pr-3">{{ $m->division?->name ?? 'HOD' }}</td>
                            <td class="py-3 pr-3 text-muted">{{ $m->jobdesc?->title ?? '–' }}</td>
                            <td class="py-3 pr-3 text-[13px] text-muted">{{ $m->user->email }}</td>
                            <td class="py-3 text-right">
                                <span class="rounded-full bg-[#e6f4ec] px-2.5 py-0.5 text-[13px] text-good">{{ ucfirst($m->status) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-muted">Tidak ada anggota yang cocok dengan pencarian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection