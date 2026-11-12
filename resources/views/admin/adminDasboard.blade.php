@extends('admin.adminView')
@section('title', 'Admin Command Center')

@section('content')
<div x-data="{
    // State Modal
    showAssignKetupelModal: false,
    showAddJobdescModal: false,
    showAddTimelineModal: false,

    // Data Proker & Ketupel (Dikelola HOD)
    prokers: [
        { id: 1, name: 'Techfest UC 2026', type: 'Repeat', ketupel: 'Sean Tandjaja', nim: '0706012310012', pj: 'Michelle Wijaya (VP)', hariH: '2026-12-12', status: 'Execution' },
        { id: 2, name: 'IMT Orientation Camp', type: 'Repeat', ketupel: 'Kevin Pratama', nim: '0706012310045', pj: 'Clarissa Tan (Pres)', hariH: '2026-12-20', status: 'Forming Committee' },
        { id: 3, name: 'National Hackathon UC', type: 'New', ketupel: 'Belum Ditunjuk', nim: '-', pj: 'Darren Lim (Sec)', hariH: '2027-01-18', status: 'Draft' }
    ],
    selectedProkerId: 1,
    formKetupel: '',
    formPj: '',

    // Data Koordinator & Jobdesc (Dikelola Ketupel)
    divisions: [
        {
            name: 'Tech',
            icon: '💻',
            koor: 'Andreas Gunawan',
            jobdescs: [
                { title: 'Deploy landing page registrasi peserta', deadline: '2026-11-20', done: true },
                { title: 'Sistem QR check-in & e-certificate generator', deadline: '2026-12-05', done: false }
            ]
        },
        {
            name: 'Event',
            icon: '🎪',
            koor: 'felicia Hartono',
            jobdescs: [
                { title: 'Finalisasi master rundown & brief pembicara', deadline: '2026-11-25', done: true },
                { title: 'Gladi bersih panggung Auditorium UC', deadline: '2026-12-11', done: false }
            ]
        },
        {
            name: 'PDD Design',
            icon: '🎨',
            koor: 'Clarissa Putri',
            jobdescs: [
                { title: 'Key visual, feeds IG, dan desain lanyard panitia', deadline: '2026-11-18', done: true },
                { title: 'LED backdrop & stage visual assets', deadline: '2026-12-04', done: false }
            ]
        },
        {
            name: 'Properties / Inventory',
            icon: '📦',
            koor: 'Bryan Santoso',
            jobdescs: [
                { title: 'Booking Auditorium UC & izin pemakaian alat', deadline: '2026-11-22', done: false }
            ]
        }
    ],
    newJobDivision: 'Tech',
    newJobTitle: '',
    newJobDeadline: '',

    // Data Timeline & Hari-H (Dikelola Ketupel)
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

    // Helper Methods
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
            target.status = 'Forming Committee';
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
}">

    <!-- 1. TOP KPI & HARI-H BANNER -->
    <section id="overview" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Hari-H Highlight Card (#1A3D63) -->
        <div class="md:col-span-2 bg-gradient-to-br from-[#0A1931] to-[#1A3D63] text-white rounded-2xl p-5 shadow-sm border border-[#4A7FA7]/40 flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <div>
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#4A7FA7] text-white">
                        Target Hari-H Proker
                    </span>
                    <h2 class="text-xl font-bold mt-2" x-text="activeProker"></h2>
                    <p class="text-xs text-[#B3CFE5] mt-0.5">Lokasi: Auditorium Universitas Ciputra, Surabaya</p>
                </div>
                <div class="text-right bg-[#0A1931]/60 px-3.5 py-2 rounded-xl border border-[#B3CFE5]/30">
                    <p class="text-[10px] uppercase tracking-wider text-[#B3CFE5]">Tanggal Pelaksanaan</p>
                    <p class="text-sm font-bold text-white mt-0.5" x-text="hariHDate"></p>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-[#4A7FA7]/30 flex items-center justify-between text-xs text-[#B3CFE5]">
                <span>Ketupel: <strong class="text-white">Sean Tandjaja</strong></span>
                <span>PJ HOD: <strong class="text-white">Michelle Wijaya (VP)</strong></span>
            </div>
        </div>

        <!-- Stat Card 2 -->
        <div class="bg-white rounded-2xl p-5 border border-[#B3CFE5]/80 shadow-2xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-[#1A3D63] uppercase">Divisi Aktif</span>
                <span class="w-8 h-8 rounded-lg bg-[#F6FAFD] border border-[#B3CFE5] flex items-center justify-center text-sm">🎯</span>
            </div>
            <div class="my-2">
                <p class="text-2xl font-bold text-[#0A1931]" x-text="divisions.length + ' Divisi'"></p>
                <p class="text-xs text-[#4A7FA7] font-medium">100% Koordinator ter-plot</p>
            </div>
        </div>

        <!-- Stat Card 3 -->
        <div class="bg-white rounded-2xl p-5 border border-[#B3CFE5]/80 shadow-2xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-[#1A3D63] uppercase">Dosen Pembina</span>
                <span class="w-8 h-8 rounded-lg bg-[#F6FAFD] border border-[#B3CFE5] flex items-center justify-center text-sm">🎓</span>
            </div>
            <div class="my-2">
                <p class="text-sm font-bold text-[#0A1931]">Dr. Ir. Budi Santoso, M.Kom.</p>
                <p class="text-xs text-emerald-700 font-medium mt-1">● Proposal Disetujui</p>
            </div>
        </div>
    </section>

    <!-- 2. HOD GOVERNANCE PANEL: ASSIGN KETUPEL & PJ -->
    <section id="hod-governance" x-show="activeRole === 'hod' || activeRole === 'dosen'"
             class="bg-white rounded-2xl border border-[#B3CFE5] shadow-2xs overflow-hidden">
        <div class="px-6 py-4 bg-[#0A1931] text-white flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="font-bold text-base">🏛️ Governance Proker & Penunjukan Ketupel (Otoritas HOD)</h3>
                <p class="text-xs text-[#B3CFE5]">HOD menunjuk Ketua Pelaksana (Ketupel) dan Penanggung Jawab (PJ) tiap Program Kerja.</p>
            </div>
            <span x-show="activeRole === 'dosen'" class="text-xs bg-[#1A3D63] text-[#B3CFE5] px-3 py-1 rounded-full border border-[#4A7FA7]">
                Mode Supervisi Dosen Pembina (Read-Only)
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-[#F6FAFD] border-b border-[#B3CFE5] text-xs uppercase text-[#1A3D63]">
                        <th class="py-3 px-6 font-semibold">Nama Proker</th>
                        <th class="py-3 px-4 font-semibold">Tipe</th>
                        <th class="py-3 px-4 font-semibold">Ketupel Ditunjuk</th>
                        <th class="py-3 px-4 font-semibold">PJ (HOD)</th>
                        <th class="py-3 px-4 font-semibold">Target Hari-H</th>
                        <th class="py-3 px-6 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#B3CFE5]/40">
                    <template x-for="proker in prokers" :key="proker.id">
                        <tr class="hover:bg-[#F6FAFD]/80 transition">
                            <td class="py-3.5 px-6 font-semibold text-[#0A1931]" x-text="proker.name"></td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#B3CFE5]/40 text-[#1A3D63]" x-text="proker.type"></span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span :class="proker.ketupel === 'Belum Ditunjuk' ? 'text-amber-700 bg-amber-50 border-amber-200' : 'text-[#0A1931] bg-[#F6FAFD] border-[#B3CFE5]'"
                                      class="px-2.5 py-1 rounded-lg border text-xs font-semibold" x-text="proker.ketupel"></span>
                            </td>
                            <td class="py-3.5 px-4 text-xs text-[#1A3D63]" x-text="proker.pj"></td>
                            <td class="py-3.5 px-4 text-xs font-medium text-[#0A1931]" x-text="proker.hariH"></td>
                            <td class="py-3.5 px-6 text-right">
                                <button x-show="activeRole === 'hod'"
                                        @click="openAssignModal(proker)"
                                        class="px-3 py-1.5 rounded-lg bg-[#4A7FA7] hover:bg-[#1A3D63] text-white text-xs font-semibold transition">
                                    Assign Ketupel
                                </button>
                                <span x-show="activeRole !== 'hod'" class="text-xs text-[#4A7FA7]">Terkunci</span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </section>

    <!-- 3. KETUPEL PANEL: MANAGE KOORDINATOR & BAGI JOBDESC TIAP DIVISI -->
    <section id="koor-jobdesc" class="bg-white rounded-2xl border border-[#B3CFE5] shadow-2xs p-6">
        <div class="flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-[#B3CFE5]/60">
            <div>
                <h3 class="font-bold text-base text-[#0A1931]">🎯 Manajemen Koordinator & Jobdesc Divisi</h3>
                <p class="text-xs text-[#1A3D63]">Ketupel menunjuk Koordinator divisi dan mendistribusikan jobdesc beserta target deadline.</p>
            </div>
            <button x-show="activeRole === 'ketupel'"
                    @click="showAddJobdescModal = true"
                    class="px-4 py-2 rounded-xl bg-[#4A7FA7] hover:bg-[#1A3D63] text-white text-xs font-semibold shadow-2xs transition">
                + Tambah Jobdesc Divisi
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">
            <template x-for="(div, idx) in divisions" :key="idx">
                <div class="rounded-xl border border-[#B3CFE5] bg-[#F6FAFD]/60 p-4 flex flex-col justify-between">
                    <div>
                        <!-- Division Header & Koor Selector -->
                        <div class="flex items-center justify-between gap-2 pb-3 border-b border-[#B3CFE5]/60">
                            <div class="flex items-center gap-2">
                                <span class="text-lg" x-text="div.icon"></span>
                                <h4 class="font-bold text-sm text-[#0A1931]" x-text="div.name"></h4>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-[11px] text-[#1A3D63]">Koor:</span>
                                <template x-if="activeRole === 'ketupel'">
                                    <select x-model="div.koor"
                                            class="bg-white border border-[#4A7FA7] rounded-lg px-2 py-1 text-xs font-semibold text-[#0A1931] focus:outline-none">
                                        <option :value="div.koor" x-text="div.koor"></option>
                                        <option value="Natasha Wijaya">Natasha Wijaya</option>
                                        <option value="Rafael Tan">Rafael Tan</option>
                                        <option value="Devin Kurniawan">Devin Kurniawan</option>
                                    </select>
                                </template>
                                <template x-if="activeRole !== 'ketupel'">
                                    <span class="text-xs font-bold text-[#1A3D63]" x-text="div.koor"></span>
                                </template>
                            </div>
                        </div>

                        <!-- Jobdesc Checklist -->
                        <ul class="mt-3 space-y-2">
                            <template x-for="(job, jIdx) in div.jobdescs" :key="jIdx">
                                <li class="bg-white p-2.5 rounded-lg border border-[#B3CFE5]/70 flex items-start justify-between gap-2 text-xs">
                                    <label class="flex items-start gap-2 cursor-pointer">
                                        <input type="checkbox" x-model="job.done" :disabled="activeRole !== 'ketupel'"
                                               class="mt-0.5 rounded border-[#4A7FA7] text-[#4A7FA7] focus:ring-0">
                                        <span :class="job.done ? 'line-through text-slate-400' : 'text-[#0A1931] font-medium'" x-text="job.title"></span>
                                    </label>
                                    <span class="shrink-0 px-2 py-0.5 rounded bg-[#B3CFE5]/40 text-[#1A3D63] font-semibold text-[10px]" x-text="job.deadline"></span>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>
            </template>
        </div>
    </section>

    <!-- 4. KETUPEL PANEL: TIMELINE RAPAT, DEADLINE DIVISI & HARI-H -->
    <section id="proker-timeline" class="bg-white rounded-2xl border border-[#B3CFE5] shadow-2xs p-6">
        <div class="flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-[#B3CFE5]/60">
            <div>
                <h3 class="font-bold text-base text-[#0A1931]">🗓️ Master Timeline Proker (Rapat, Deadline Divisi & Hari-H)</h3>
                <p class="text-xs text-[#1A3D63]">Jadwal terpusat yang otomatis tersinkronisasi ke dashboard seluruh Koordinator & Staff.</p>
            </div>
            <button x-show="activeRole === 'ketupel'"
                    @click="showAddTimelineModal = true"
                    class="px-4 py-2 rounded-xl bg-[#0A1931] hover:bg-[#1A3D63] text-white text-xs font-semibold transition">
                + Set Jadwal / Hari-H
            </button>
        </div>

        <div class="mt-5 relative border-l-2 border-[#4A7FA7] ml-3 space-y-4">
            <template x-for="item in timelines" :key="item.id">
                <div class="relative pl-6">
                    <!-- Dot indicator -->
                    <span :class="{
                        'bg-[#0A1931] ring-4 ring-[#B3CFE5]': item.category === 'Hari-H',
                        'bg-[#4A7FA7]': item.category === 'Rapat',
                        'bg-[#1A3D63]': item.category === 'Deadline'
                    }" class="absolute -left-[9px] top-2 w-4 h-4 rounded-full border-2 border-white"></span>

                    <div :class="item.category === 'Hari-H' ? 'bg-[#0A1931] text-white border-[#0A1931]' : 'bg-[#F6FAFD] text-[#0A1931] border-[#B3CFE5]'"
                         class="p-4 rounded-xl border flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <span :class="{
                                    'bg-[#4A7FA7] text-white': item.category === 'Hari-H',
                                    'bg-[#B3CFE5] text-[#0A1931]': item.category === 'Rapat',
                                    'bg-[#1A3D63] text-white': item.category === 'Deadline'
                                }" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider" x-text="item.category"></span>
                                <span :class="item.category === 'Hari-H' ? 'text-[#B3CFE5]' : 'text-[#1A3D63]'" class="text-xs font-semibold" x-text="item.date + ' • ' + item.time"></span>
                            </div>
                            <h4 class="font-bold text-sm mt-1" x-text="item.title"></h4>
                        </div>
                        <span :class="item.category === 'Hari-H' ? 'bg-[#1A3D63] text-[#B3CFE5]' : 'bg-white text-[#1A3D63] border border-[#B3CFE5]'"
                              class="px-3 py-1 rounded-lg text-xs font-medium" x-text="'PIC: ' + item.owner"></span>
                    </div>
                </div>
            </template>
        </div>
    </section>

    <!-- 5. DOSEN PEMBINA ADVISORY & ESCALATION PANEL -->
    <section id="dosen-advisory" x-show="activeRole === 'dosen' || activeRole === 'hod'"
             class="bg-[#F6FAFD] rounded-2xl border-2 border-[#4A7FA7]/50 p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="font-bold text-base text-[#0A1931]">🎓 Ruang Supervisi & Eskalasi Dosen Pembina</h3>
                <p class="text-xs text-[#1A3D63]">Catatan pembinaan untuk kebuntuan operasional, perizinan kampus UC, dan pengesahan LPJ.</p>
            </div>
            <button x-show="activeRole === 'dosen'"
                    class="px-4 py-2 rounded-xl bg-[#1A3D63] hover:bg-[#0A1931] text-white text-xs font-semibold transition">
                ✓ Sahkan Milestone / Proposal
            </button>
        </div>
        <div class="mt-4 bg-white p-4 rounded-xl border border-[#B3CFE5] text-xs text-[#0A1931] space-y-2">
            <p class="font-semibold text-[#1A3D63]">Catatan Terakhir Dosen Pembina (Dr. Ir. Budi Santoso, M.Kom.):</p>
            <p class="leading-relaxed text-slate-700">"Pastikan pengajuan peminjaman Auditorium UC dan surat izin sponsor eksternal sudah masuk ke Biro Kemahasiswaan maksimal H-14 sebelum acara."</p>
        </div>
    </section>

    <!-- MODAL 1: HOD ASSIGN KETUPEL -->
    <div x-show="showAssignKetupelModal" x-cloak class="fixed inset-0 z-50 bg-[#0A1931]/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.outside="showAssignKetupelModal = false" class="bg-white rounded-2xl max-w-md w-full border border-[#B3CFE5] shadow-xl overflow-hidden">
            <div class="px-6 py-4 bg-[#0A1931] text-white flex justify-between items-center">
                <h4 class="font-bold text-sm">Assign Ketupel & PJ Proker</h4>
                <button @click="showAssignKetupelModal = false" class="text-[#B3CFE5] hover:text-white">✕</button>
            </div>
            <div class="p-6 space-y-4 text-sm">
                <div>
                    <label class="block text-xs font-bold text-[#1A3D63] mb-1">Pilih Mahasiswa (Anggota SU IMT)</label>
                    <select x-model="formKetupel" class="w-full rounded-xl border border-[#B3CFE5] px-3 py-2 text-xs focus:outline-none focus:border-[#4A7FA7]">
                        <option value="">-- Pilih Ketupel --</option>
                        <option value="Sean Tandjaja">Sean Tandjaja (IMT '23)</option>
                        <option value="Kevin Pratama">Kevin Pratama (IMT '23)</option>
                        <option value="jessica Lim">Jessica Lim (IMT '24)</option>
                        <option value="Matthew Wijaya">Matthew Wijaya (IMT '23)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#1A3D63] mb-1">Penanggung Jawab (HOD)</label>
                    <select x-model="formPj" class="w-full rounded-xl border border-[#B3CFE5] px-3 py-2 text-xs focus:outline-none focus:border-[#4A7FA7]">
                        <option value="Clarissa Tan (Pres)">Clarissa Tan (President)</option>
                        <option value="Michelle Wijaya (VP)">Michelle Wijaya (Vice President)</option>
                        <option value="Darren Lim (Sec)">Darren Lim (Secretary)</option>
                        <option value="Stephanie Go (Treas)">Stephanie Go (Treasurer)</option>
                    </select>
                </div>
                <div class="pt-2 flex justify-end gap-2">
                    <button @click="showAssignKetupelModal = false" class="px-4 py-2 rounded-xl border border-[#B3CFE5] text-xs font-semibold text-[#1A3D63]">Batal</button>
                    <button @click="saveKetupelAssignment()" class="px-4 py-2 rounded-xl bg-[#4A7FA7] text-white text-xs font-semibold">Simpan Penugasan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 2: KETUPEL TAMBAH JOBDESC -->
    <div x-show="showAddJobdescModal" x-cloak class="fixed inset-0 z-50 bg-[#0A1931]/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.outside="showAddJobdescModal = false" class="bg-white rounded-2xl max-w-md w-full border border-[#B3CFE5] shadow-xl overflow-hidden">
            <div class="px-6 py-4 bg-[#0A1931] text-white flex justify-between items-center">
                <h4 class="font-bold text-sm">Tambah Jobdesc Divisi</h4>
                <button @click="showAddJobdescModal = false" class="text-[#B3CFE5] hover:text-white">✕</button>
            </div>
            <div class="p-6 space-y-4 text-sm">
                <div>
                    <label class="block text-xs font-bold text-[#1A3D63] mb-1">Divisi Tujuan</label>
                    <select x-model="newJobDivision" class="w-full rounded-xl border border-[#B3CFE5] px-3 py-2 text-xs">
                        <template x-for="d in divisions" :key="d.name">
                            <option :value="d.name" x-text="d.name + ' (Koor: ' + d.koor + ')'"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#1A3D63] mb-1">Deskripsi Jobdesc</label>
                    <input type="text" x-model="newJobTitle" placeholder="Contoh: Finalisasi sponsorship paket Gold"
                           class="w-full rounded-xl border border-[#B3CFE5] px-3 py-2 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#1A3D63] mb-1">Deadline Divisi</label>
                    <input type="date" x-model="newJobDeadline" class="w-full rounded-xl border border-[#B3CFE5] px-3 py-2 text-xs">
                </div>
                <div class="pt-2 flex justify-end gap-2">
                    <button @click="showAddJobdescModal = false" class="px-4 py-2 rounded-xl border border-[#B3CFE5] text-xs font-semibold text-[#1A3D63]">Batal</button>
                    <button @click="addJobdesc()" class="px-4 py-2 rounded-xl bg-[#4A7FA7] text-white text-xs font-semibold">Distribusikan Jobdesc</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 3: KETUPEL SET TIMELINE / HARI-H -->
    <div x-show="showAddTimelineModal" x-cloak class="fixed inset-0 z-50 bg-[#0A1931]/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.outside="showAddTimelineModal = false" class="bg-white rounded-2xl max-w-md w-full border border-[#B3CFE5] shadow-xl overflow-hidden">
            <div class="px-6 py-4 bg-[#0A1931] text-white flex justify-between items-center">
                <h4 class="font-bold text-sm">Set Jadwal Rapat, Deadline, atau Hari-H</h4>
                <button @click="showAddTimelineModal = false" class="text-[#B3CFE5] hover:text-white">✕</button>
            </div>
            <div class="p-6 space-y-4 text-sm">
                <div>
                    <label class="block text-xs font-bold text-[#1A3D63] mb-1">Kategori Agenda</label>
                    <select x-model="newTlCategory" class="w-full rounded-xl border border-[#B3CFE5] px-3 py-2 text-xs">
                        <option value="Rapat">🗣️ Rapat Koordinasi / Pleno</option>
                        <option value="Deadline">⏳ Deadline Tugas Divisi</option>
                        <option value="Hari-H">🚀 Hari-H Pelaksanaan Proker</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#1A3D63] mb-1">Judul Agenda / Milestone</label>
                    <input type="text" x-model="newTlTitle" placeholder="Contoh: Rapat Pleno II / Hari-H Techfest"
                           class="w-full rounded-xl border border-[#B3CFE5] px-3 py-2 text-xs">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-[#1A3D63] mb-1">Tanggal</label>
                        <input type="date" x-model="newTlDate" class="w-full rounded-xl border border-[#B3CFE5] px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#1A3D63] mb-1">Waktu</label>
                        <input type="text" x-model="newTlTime" class="w-full rounded-xl border border-[#B3CFE5] px-3 py-2 text-xs">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#1A3D63] mb-1">Target Divisi / PIC</label>
                    <input type="text" x-model="newTlOwner" class="w-full rounded-xl border border-[#B3CFE5] px-3 py-2 text-xs">
                </div>
                <div class="pt-2 flex justify-end gap-2">
                    <button @click="showAddTimelineModal = false" class="px-4 py-2 rounded-xl border border-[#B3CFE5] text-xs font-semibold text-[#1A3D63]">Batal</button>
                    <button @click="addTimeline()" class="px-4 py-2 rounded-xl bg-[#0A1931] text-white text-xs font-semibold">Simpan ke Timeline</button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection