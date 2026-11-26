@extends('layouts.app')
@section('title', 'Admin Command Center — Himpun')

@section('content')
<div x-data="{
    activeProker: 'Techfest UC 2026',
    showAssignKetupelModal: false,
    showAddJobdescModal: false,
    showAddTimelineModal: false,

    prokers: [
        { id: 1, name: 'Techfest UC 2026', type: 'Ulangi', ketupel: 'Rafael Santoso', nim: '0706012310012', pj: 'Michelle Wijaya', hariH: '2026-12-12', status: 'Berjalan' },
        { id: 2, name: 'IMT Orientation Camp', type: 'Ulangi', ketupel: 'Kevin Pratama', nim: '0706012310045', pj: 'Clarissa Tan', hariH: '2026-12-20', status: 'Pembentukan panitia' },
        { id: 3, name: 'National Hackathon UC', type: 'Baru', ketupel: 'Belum Ditunjuk', nim: '-', pj: 'Darren Lim', hariH: '2027-01-18', status: 'Usulan' }
    ],
    selectedProkerId: 1,
    formKetupel: '',
    formPj: '',

    divisions: [
        {
            name: 'Tech',
            koor: 'Andreas Gunawan',
            jobdescs: [
                { title: 'Deploy landing page registrasi peserta', deadline: '2026-11-20', done: true },
                { title: 'Sistem QR check-in & e-certificate generator', deadline: '2026-12-05', done: false }
            ]
        },
        {
            name: 'Event',
            koor: 'Felicia Hartono',
            jobdescs: [
                { title: 'Finalisasi master rundown & brief pembicara', deadline: '2026-11-25', done: true },
                { title: 'Gladi bersih panggung Auditorium UC', deadline: '2026-12-11', done: false }
            ]
        },
        {
            name: 'PDD Design',
            koor: 'Clarissa Putri',
            jobdescs: [
                { title: 'Key visual, feeds IG, dan desain lanyard panitia', deadline: '2026-11-18', done: true },
                { title: 'LED backdrop & stage visual assets', deadline: '2026-12-04', done: false }
            ]
        },
        {
            name: 'Properties',
            koor: 'Bryan Santoso',
            jobdescs: [
                { title: 'Booking Auditorium UC & izin pemakaian alat', deadline: '2026-11-22', done: false }
            ]
        }
    ],
    newJobDivision: 'Tech',
    newJobTitle: '',
    newJobDeadline: '',

    hariHDate: '2026-12-12',
    timelines: [
        { id: 1, category: 'Rapat', title: 'Rapat Pleno I — Sinkronisasi Konsep & RAB', date: '2026-11-15', time: '18:30 WIB', owner: 'Semua Divisi' },
        { id: 2, category: 'Deadline', title: 'Close Registration & Publikasi Gelombang 1', date: '2026-11-25', time: '23:59 WIB', owner: 'Tech & Marketing' },
        { id: 3, category: 'Rapat', title: 'Rapat Koor & HOD/PJ — Cek Kesiapan Vendor', date: '2026-12-02', time: '19:00 WIB', owner: 'Ketupel, HOD/PJ, Koor' },
        { id: 4, category: 'Deadline', title: 'Pengumpulan Seluruh Aset Visual & Rundown Final', date: '2026-12-08', time: '21:00 WIB', owner: 'PDD Design & Event' },
        { id: 5, category: 'Hari-H', title: 'HARI-H: Main Event Techfest UC 2026', date: '2026-12-12', time: '08:00 WIB', owner: 'Seluruh Panitia' }
    ],
    newTlCategory: 'Rapat',
    newTlTitle: '',
    newTlDate: '',
    newTlTime: '18:30 WIB',
    newTlOwner: 'Semua Divisi',

    openAssignModal(proker) {
        this.selectedProkerId = proker.id;
        this.formKetupel = proker.ketupel === 'Belum Ditunjuk' ? '' : proker.ketupel;
        this.formPj = proker.pj;
        this.showAssignKetupelModal = true;
    },
    saveKetupelAssignment() {
        const target = this.prokers.find(p => p.id === this.selectedProkerId);
        if (target && this.formKetupel) {
            target.ketupel = this.formKetupel;
            target.pj = this.formPj;
            target.status = 'Pembentukan panitia';
        }
        this.showAssignKetupelModal = false;
    },
    addJobdesc() {
        if (!this.newJobTitle || !this.newJobDeadline) return;
        const div = this.divisions.find(d => d.name === this.newJobDivision);
        if (div) {
            div.jobdescs.push({ title: this.newJobTitle, deadline: this.newJobDeadline, done: false });
        }
        this.newJobTitle = '';
        this.newJobDeadline = '';
        this.showAddJobdescModal = false;
    },
    addTimeline() {
        if (!this.newTlTitle || !this.newTlDate) return;
        if (this.newTlCategory === 'Hari-H') {
            this.hariHDate = this.newTlDate;
        }
        this.timelines.push({
            id: Date.now(),
            category: this.newTlCategory,
            title: this.newTlTitle,
            date: this.newTlDate,
            time: this.newTlTime,
            owner: this.newTlOwner
        });
        this.timelines.sort((a, b) => a.date.localeCompare(b.date));
        this.newTlTitle = '';
        this.newTlDate = '';
        this.showAddTimelineModal = false;
    }
}" class="flex flex-col gap-6">

    {{-- Header --}}
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-[22px] font-semibold leading-tight">Admin Command Center</h1>
            <p class="mt-0.5 text-muted">Governance proker, penunjukan Ketupel, dan monitoring divisi · SU IMT</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('members.index') }}" class="inline-flex h-9 items-center rounded-[10px] border border-line px-3.5 text-[13px] font-medium hover:bg-black/5">Kelola Anggota</a>
            <a href="{{ route('divisions.index') }}" class="inline-flex h-9 items-center rounded-[10px] bg-accent px-4 text-[13px] font-medium text-white">Divisi & Jobdesc</a>
        </div>
    </div>

    {{-- Top KPI Strip --}}
    <dl class="grid grid-cols-1 gap-px overflow-hidden rounded-xl border border-line bg-line md:grid-cols-4">
        <div class="bg-white p-4 md:col-span-2">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <dt class="text-[13px] text-muted">Target Hari-H Proker Aktif</dt>
                    <dd class="mt-1 text-lg font-semibold" x-text="activeProker"></dd>
                    <p class="mt-1 text-[13px] text-muted">Auditorium Universitas Ciputra · Ketupel: <span class="text-ink">Rafael Santoso</span></p>
                </div>
                <span class="rounded-lg border border-line bg-frame px-3 py-1.5 text-right tabular-nums">
                    <span class="block text-[11px] uppercase text-muted">Hari-H</span>
                    <span class="text-[13px] font-semibold" x-text="hariHDate"></span>
                </span>
            </div>
        </div>
        <div class="bg-white p-4">
            <dt class="text-[13px] text-muted">Divisi Aktif</dt>
            <dd class="mt-1 text-[28px] font-normal leading-none tabular-nums" x-text="divisions.length"></dd>
            <p class="mt-2 text-[13px] text-muted">100% koordinator ter-plot</p>
        </div>
        <div class="bg-white p-4">
            <dt class="text-[13px] text-muted">Dosen Pembina</dt>
            <dd class="mt-1 text-base font-medium">Dr. Ir. Budi Santoso</dd>
            <p class="mt-2 text-[13px] text-good">Proposal disetujui</p>
        </div>
    </dl>

    {{-- HOD Governance Panel --}}
    <section class="rounded-xl border border-line p-5">
        <div class="flex flex-wrap items-baseline justify-between gap-2">
            <div>
                <h2 class="text-base font-semibold">Governance Proker & Penunjukan Ketupel</h2>
                <p class="mt-0.5 text-[13px] text-muted">HOD menunjuk Ketua Pelaksana (Ketupel) dan Penanggung Jawab (PJ) tiap Program Kerja.</p>
            </div>
        </div>

        <div class="mt-3 overflow-x-auto">
            <table class="w-full min-w-[640px] text-left">
                <thead class="text-[13px] text-muted">
                    <tr>
                        <th class="py-2 font-normal">Nama Proker</th>
                        <th class="py-2 font-normal">Tipe</th>
                        <th class="py-2 font-normal">Ketupel</th>
                        <th class="py-2 font-normal">PJ (HOD)</th>
                        <th class="py-2 font-normal">Target Hari-H</th>
                        <th class="py-2 text-right font-normal">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line border-t border-line">
                    <template x-for="proker in prokers" :key="proker.id">
                        <tr>
                            <td class="py-3 pr-3 font-medium" x-text="proker.name"></td>
                            <td class="py-3 pr-3 text-muted" x-text="proker.type"></td>
                            <td class="py-3 pr-3">
                                <span :class="proker.ketupel === 'Belum Ditunjuk' ? 'bg-[#fbf0dc] text-warn' : 'bg-black/5 text-ink'"
                                      class="rounded-full px-2.5 py-0.5 text-[13px]" x-text="proker.ketupel"></span>
                            </td>
                            <td class="py-3 pr-3 text-muted" x-text="proker.pj"></td>
                            <td class="py-3 pr-3 tabular-nums" x-text="proker.hariH"></td>
                            <td class="py-3 text-right">
                                <button type="button" @click="openAssignModal(proker)"
                                        class="h-8 rounded-lg border border-line px-3 text-[13px] font-medium hover:bg-black/5">
                                    Assign Ketupel
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </section>

    {{-- Koordinator & Jobdesc Panel --}}
    <section class="rounded-xl border border-line p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-semibold">Koordinator & Jobdesc Divisi</h2>
                <p class="mt-0.5 text-[13px] text-muted">Pantau distribusi tugas tiap divisi dan tenggat waktu pelaksanaan.</p>
            </div>
            <button type="button" @click="showAddJobdescModal = true"
                    class="inline-flex h-8 items-center rounded-lg bg-accent px-3 text-[13px] font-medium text-white">
                + Tambah Jobdesc
            </button>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            <template x-for="(div, idx) in divisions" :key="idx">
                <div class="rounded-xl border border-line p-4">
                    <div class="flex items-center justify-between gap-2 border-b border-line pb-3">
                        <h3 class="font-semibold" x-text="div.name"></h3>
                        <span class="text-[13px] text-muted">Koor: <strong class="font-medium text-ink" x-text="div.koor"></strong></span>
                    </div>
                    <ul class="mt-3 space-y-2">
                        <template x-for="(job, jIdx) in div.jobdescs" :key="jIdx">
                            <li class="flex items-start justify-between gap-2 rounded-lg bg-frame/60 p-2.5 text-[13px]">
                                <label class="flex cursor-pointer items-start gap-2">
                                    <input type="checkbox" x-model="job.done" class="mt-0.5 rounded border-line">
                                    <span :class="job.done ? 'line-through text-muted' : ''" x-text="job.title"></span>
                                </label>
                                <span class="shrink-0 tabular-nums text-muted" x-text="job.deadline"></span>
                            </li>
                        </template>
                    </ul>
                </div>
            </template>
        </div>
    </section>

    {{-- Timeline & Rapat Panel --}}
    <section class="rounded-xl border border-line p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-semibold">Timeline & Jadwal Rapat</h2>
                <p class="mt-0.5 text-[13px] text-muted">Agenda rapat pleno, tenggat divisi, dan pelaksanaan Hari-H.</p>
            </div>
            <button type="button" @click="showAddTimelineModal = true"
                    class="inline-flex h-8 items-center rounded-lg border border-line px-3 text-[13px] font-medium hover:bg-black/5">
                + Tambah Agenda
            </button>
        </div>

        <ul class="mt-3 divide-y divide-line border-t border-line">
            <template x-for="tl in timelines" :key="tl.id">
                <li class="flex flex-wrap items-center justify-between gap-3 py-3">
                    <div class="flex items-center gap-3">
                        <span class="rounded-full bg-black/5 px-2.5 py-0.5 text-[13px]" x-text="tl.category"></span>
                        <div>
                            <p class="font-medium" x-text="tl.title"></p>
                            <p class="text-[13px] text-muted" x-text="tl.owner"></p>
                        </div>
                    </div>
                    <span class="text-[13px] tabular-nums text-muted" x-text="tl.date + ' · ' + tl.time"></span>
                </li>
            </template>
        </ul>
    </section>

    {{-- Modal: Assign Ketupel --}}
    <div x-show="showAssignKetupelModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
        <div @click.outside="showAssignKetupelModal = false" class="w-full max-w-md rounded-2xl border border-line bg-white p-6">
            <h3 class="text-base font-semibold">Assign Ketupel & PJ</h3>
            <div class="mt-4 space-y-3">
                <div>
                    <label class="block text-[13px] text-muted">Nama Ketupel</label>
                    <input type="text" x-model="formKetupel" placeholder="Contoh: Rafael Santoso" class="mt-1 h-9 w-full rounded-lg border border-line px-3 text-sm">
                </div>
                <div>
                    <label class="block text-[13px] text-muted">Penanggung Jawab (HOD)</label>
                    <input type="text" x-model="formPj" placeholder="Contoh: Michelle Wijaya" class="mt-1 h-9 w-full rounded-lg border border-line px-3 text-sm">
                </div>
            </div>
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" @click="showAssignKetupelModal = false" class="h-9 rounded-lg border border-line px-3.5 text-[13px]">Batal</button>
                <button type="button" @click="saveKetupelAssignment()" class="h-9 rounded-lg bg-accent px-4 text-[13px] font-medium text-white">Simpan</button>
            </div>
        </div>
    </div>

    {{-- Modal: Tambah Jobdesc --}}
    <div x-show="showAddJobdescModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
        <div @click.outside="showAddJobdescModal = false" class="w-full max-w-md rounded-2xl border border-line bg-white p-6">
            <h3 class="text-base font-semibold">Tambah Jobdesc Divisi</h3>
            <div class="mt-4 space-y-3">
                <div>
                    <label class="block text-[13px] text-muted">Divisi</label>
                    <select x-model="newJobDivision" class="mt-1 h-9 w-full rounded-lg border border-line px-3 text-sm">
                        <template x-for="div in divisions" :key="div.name">
                            <option :value="div.name" x-text="div.name"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-[13px] text-muted">Judul Jobdesc</label>
                    <input type="text" x-model="newJobTitle" placeholder="Deskripsi tugas..." class="mt-1 h-9 w-full rounded-lg border border-line px-3 text-sm">
                </div>
                <div>
                    <label class="block text-[13px] text-muted">Deadline</label>
                    <input type="date" x-model="newJobDeadline" class="mt-1 h-9 w-full rounded-lg border border-line px-3 text-sm">
                </div>
            </div>
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" @click="showAddJobdescModal = false" class="h-9 rounded-lg border border-line px-3.5 text-[13px]">Batal</button>
                <button type="button" @click="addJobdesc()" class="h-9 rounded-lg bg-accent px-4 text-[13px] font-medium text-white">Tambah</button>
            </div>
        </div>
    </div>

    {{-- Modal: Tambah Agenda --}}
    <div x-show="showAddTimelineModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
        <div @click.outside="showAddTimelineModal = false" class="w-full max-w-md rounded-2xl border border-line bg-white p-6">
            <h3 class="text-base font-semibold">Tambah Agenda Timeline</h3>
            <div class="mt-4 space-y-3">
                <div>
                    <label class="block text-[13px] text-muted">Kategori</label>
                    <select x-model="newTlCategory" class="mt-1 h-9 w-full rounded-lg border border-line px-3 text-sm">
                        <option value="Rapat">Rapat</option>
                        <option value="Deadline">Deadline</option>
                        <option value="Hari-H">Hari-H</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[13px] text-muted">Judul Agenda</label>
                    <input type="text" x-model="newTlTitle" placeholder="Contoh: Rapat Pleno II" class="mt-1 h-9 w-full rounded-lg border border-line px-3 text-sm">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[13px] text-muted">Tanggal</label>
                        <input type="date" x-model="newTlDate" class="mt-1 h-9 w-full rounded-lg border border-line px-3 text-sm">
                    </div>
                    <div>
                        <label class="block text-[13px] text-muted">Waktu</label>
                        <input type="text" x-model="newTlTime" class="mt-1 h-9 w-full rounded-lg border border-line px-3 text-sm">
                    </div>
                </div>
            </div>
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" @click="showAddTimelineModal = false" class="h-9 rounded-lg border border-line px-3.5 text-[13px]">Batal</button>
                <button type="button" @click="addTimeline()" class="h-9 rounded-lg bg-accent px-4 text-[13px] font-medium text-white">Simpan</button>
            </div>
        </div>
    </div>
</div>
@endsection