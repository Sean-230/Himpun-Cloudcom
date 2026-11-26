@extends('layouts.app')
@section('title', 'Divisi & Jobdesc — Himpun')

@section('content')
<div x-data="{
    showNewDivision: false,
    showNewJobdesc: false,
    editDivId: null,
    editDivName: '',
    editJobId: null,
    editJobTitle: '',
    editJobLevel: 'staff',
    editJobDesc: ''
}" class="flex flex-col gap-6">

    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-[22px] font-semibold leading-tight">Divisi & Jobdesc</h1>
            <p class="mt-0.5 text-muted">Master data divisi tetap dan deskripsi peran organisasi · {{ $organisation?->name ?? 'SU IMT' }}</p>
        </div>
        <div class="flex gap-2">
            <button type="button" @click="showNewJobdesc = true" class="inline-flex h-9 items-center rounded-[10px] border border-line px-3.5 text-[13px] font-medium hover:bg-black/5">+ Tambah Jobdesc</button>
            <button type="button" @click="showNewDivision = true" class="inline-flex h-9 items-center rounded-[10px] bg-accent px-4 text-[13px] font-medium text-white">+ Tambah Divisi</button>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-line bg-[#e6f4ec] px-4 py-3 text-[13px] text-good">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-line bg-[#fff2f2] px-4 py-3 text-[13px] text-red-700">{{ $errors->first() }}</div>
    @endif

    {{-- Standing Divisions Grid --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        @foreach ($divisions as $division)
            <section class="rounded-xl border border-line p-5">
                <div class="flex items-center justify-between gap-2 border-b border-line pb-3">
                    <div>
                        <h2 class="text-base font-semibold">{{ $division->name }}</h2>
                        <p class="text-[13px] text-muted">{{ $division->memberships_count }} anggota aktif</p>
                    </div>
                    <button type="button"
                            @click="editDivId = {{ $division->id }}; editDivName = @js($division->name)"
                            class="h-8 rounded-lg border border-line px-3 text-[13px] hover:bg-black/5">
                        Edit Divisi
                    </button>
                </div>

                <ul class="mt-3 space-y-2">
                    @forelse ($division->jobdescs as $job)
                        <li class="flex items-start justify-between gap-3 rounded-lg bg-frame/60 p-3">
                            <div>
                                <p class="font-medium">{{ $job->title }} <span class="ml-1 rounded bg-black/5 px-1.5 py-0.5 text-[11px] uppercase text-muted">{{ $job->level }}</span></p>
                                @if ($job->description)
                                    <p class="mt-0.5 text-[13px] text-muted">{{ $job->description }}</p>
                                @endif
                            </div>
                            <button type="button"
                                    @click="editJobId = {{ $job->id }}; editJobTitle = @js($job->title); editJobLevel = @js($job->level); editJobDesc = @js($job->description ?? '')"
                                    class="shrink-0 text-[13px] text-accent hover:underline">
                                Edit
                            </button>
                        </li>
                    @empty
                        <li class="py-2 text-[13px] text-muted">Belum ada jobdesc terdaftar untuk divisi ini.</li>
                    @endforelse
                </ul>
            </section>
        @endforeach
    </div>

    {{-- Executive Board (HOD) Jobdescs --}}
    <section class="rounded-xl border border-line p-5">
        <h2 class="text-base font-semibold">Executive Board (HOD)</h2>
        <p class="mt-0.5 text-[13px] text-muted">Peran struktural inti di luar divisi operasional.</p>
        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($hodJobdescs as $job)
                <div class="rounded-lg border border-line p-3">
                    <div class="flex items-center justify-between gap-2">
                        <p class="font-medium">{{ $job->title }}</p>
                        <button type="button"
                                @click="editJobId = {{ $job->id }}; editJobTitle = @js($job->title); editJobLevel = @js($job->level); editJobDesc = @js($job->description ?? '')"
                                class="text-[13px] text-accent hover:underline">Edit</button>
                    </div>
                    <p class="mt-1 text-[13px] text-muted">{{ $job->description ?? 'Executive Board' }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Modal: Create Division --}}
    <div x-show="showNewDivision" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
        <form method="POST" action="{{ route('divisions.store') }}" @click.outside="showNewDivision = false" class="w-full max-w-md rounded-2xl border border-line bg-white p-6">
            @csrf
            <h3 class="text-base font-semibold">Tambah Divisi Baru</h3>
            <div class="mt-4">
                <label class="block text-[13px] text-muted">Nama Divisi</label>
                <input type="text" name="name" required placeholder="Contoh: Marketing" class="mt-1 h-9 w-full rounded-lg border border-line px-3 text-sm">
            </div>
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" @click="showNewDivision = false" class="h-9 rounded-lg border border-line px-3.5 text-[13px]">Batal</button>
                <button type="submit" class="h-9 rounded-lg bg-accent px-4 text-[13px] font-medium text-white">Simpan</button>
            </div>
        </form>
    </div>

    {{-- Modal: Edit Division --}}
    <div x-show="editDivId !== null" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
        <form method="POST" :action="'/divisi/' + editDivId" @click.outside="editDivId = null" class="w-full max-w-md rounded-2xl border border-line bg-white p-6">
            @csrf
            @method('PATCH')
            <h3 class="text-base font-semibold">Edit Nama Divisi</h3>
            <div class="mt-4">
                <label class="block text-[13px] text-muted">Nama Divisi</label>
                <input type="text" name="name" x-model="editDivName" required class="mt-1 h-9 w-full rounded-lg border border-line px-3 text-sm">
            </div>
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" @click="editDivId = null" class="h-9 rounded-lg border border-line px-3.5 text-[13px]">Batal</button>
                <button type="submit" class="h-9 rounded-lg bg-accent px-4 text-[13px] font-medium text-white">Update</button>
            </div>
        </form>
    </div>

    {{-- Modal: Create Jobdesc --}}
    <div x-show="showNewJobdesc" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
        <form method="POST" action="{{ route('jobdescs.store') }}" @click.outside="showNewJobdesc = false" class="w-full max-w-md rounded-2xl border border-line bg-white p-6">
            @csrf
            <h3 class="text-base font-semibold">Tambah Jobdesc Baru</h3>
            <div class="mt-4 space-y-3">
                <div>
                    <label class="block text-[13px] text-muted">Divisi (kosongkan untuk HOD)</label>
                    <select name="division_id" class="mt-1 h-9 w-full rounded-lg border border-line px-3 text-sm">
                        <option value="">HOD (Executive Board)</option>
                        @foreach ($divisions as $div)
                            <option value="{{ $div->id }}">{{ $div->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[13px] text-muted">Judul Jabatan / Jobdesc</label>
                    <input type="text" name="title" required placeholder="Contoh: Koordinator / Staff" class="mt-1 h-9 w-full rounded-lg border border-line px-3 text-sm">
                </div>
                <div>
                    <label class="block text-[13px] text-muted">Level</label>
                    <select name="level" required class="mt-1 h-9 w-full rounded-lg border border-line px-3 text-sm">
                        <option value="staff">Staff</option>
                        <option value="koordinator">Koordinator</option>
                        <option value="hod">HOD</option>
                        <option value="dosen">Dosen Pembina</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[13px] text-muted">Deskripsi</label>
                    <textarea name="description" rows="2" class="mt-1 w-full rounded-lg border border-line p-2.5 text-sm"></textarea>
                </div>
            </div>
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" @click="showNewJobdesc = false" class="h-9 rounded-lg border border-line px-3.5 text-[13px]">Batal</button>
                <button type="submit" class="h-9 rounded-lg bg-accent px-4 text-[13px] font-medium text-white">Simpan</button>
            </div>
        </form>
    </div>

    {{-- Modal: Edit Jobdesc --}}
    <div x-show="editJobId !== null" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
        <form method="POST" :action="'/jobdesc/' + editJobId" @click.outside="editJobId = null" class="w-full max-w-md rounded-2xl border border-line bg-white p-6">
            @csrf
            @method('PATCH')
            <h3 class="text-base font-semibold">Edit Jobdesc</h3>
            <div class="mt-4 space-y-3">
                <div>
                    <label class="block text-[13px] text-muted">Judul Jabatan / Jobdesc</label>
                    <input type="text" name="title" x-model="editJobTitle" required class="mt-1 h-9 w-full rounded-lg border border-line px-3 text-sm">
                </div>
                <div>
                    <label class="block text-[13px] text-muted">Level</label>
                    <select name="level" x-model="editJobLevel" required class="mt-1 h-9 w-full rounded-lg border border-line px-3 text-sm">
                        <option value="staff">Staff</option>
                        <option value="koordinator">Koordinator</option>
                        <option value="hod">HOD</option>
                        <option value="dosen">Dosen Pembina</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[13px] text-muted">Deskripsi</label>
                    <textarea name="description" x-model="editJobDesc" rows="2" class="mt-1 w-full rounded-lg border border-line p-2.5 text-sm"></textarea>
                </div>
            </div>
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" @click="editJobId = null" class="h-9 rounded-lg border border-line px-3.5 text-[13px]">Batal</button>
                <button type="submit" class="h-9 rounded-lg bg-accent px-4 text-[13px] font-medium text-white">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection