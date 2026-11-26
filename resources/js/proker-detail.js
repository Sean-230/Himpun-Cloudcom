// State for the proker detail screen: jobdesc targets per division and the weekly meeting schedule.
import { addDays, fmtLong, fmtShort, targetLabel } from './dates.js';

const DAYS = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
const WEEK = [1, 2, 3, 4, 5, 6, 0]; // Senin first
let nextId = 1000;

export default (proker, { today = new Date().toLocaleDateString('en-CA'), canEdit = true } = {}) => ({
    proker,
    today,
    canEdit, // only the HOD edits; the server decides, this just keeps the controls honest
    weekdays: WEEK.map((day) => ({ day, name: DAYS[day] })),
    divisions: proker.divisions.map((d) => ({ ...d, jobdesc: d.jobdesc.map((j) => ({ id: ++nextId, ...j })) })),
    rapat: proker.rapat.map((r) => ({ ...r })),
    jobdescDraft: Object.fromEntries(proker.divisions.map((d) => [d.name, { title: '', h: 7 }])),
    jobdescError: {},
    rapatDraft: { scope: 'pleno', day: '4', time: '19:00', place: '' },
    rapatError: '',

    targetLabel,
    fmtShort,
    fmtLong,
    dateFor(h) {
        return addDays(this.proker.hariH, -h);
    },
    // Done, overdue (target date passed and not done) or still to do.
    status(j) {
        if (j.done) return 'Selesai';
        return this.dateFor(j.h) < this.today ? 'Terlambat' : 'Belum';
    },
    progress(div) {
        const done = div.jobdesc.filter((j) => j.done).length;
        const total = div.jobdesc.length;
        return { done, total, pct: total ? Math.round((done / total) * 100) : 0 };
    },
    get totals() {
        const all = this.divisions.flatMap((d) => d.jobdesc);
        return {
            done: all.filter((j) => j.done).length,
            total: all.length,
            overdue: all.filter((j) => this.status(j) === 'Terlambat').length,
            koor: this.divisions.reduce((n, d) => n + d.koor.length, 0),
            anggota: this.divisions.reduce((n, d) => n + d.anggota.length, 0),
        };
    },
    get daysLeft() {
        return Math.round((new Date(`${this.proker.hariH}T00:00:00`) - new Date(`${this.today}T00:00:00`)) / 86400000);
    },

    addJobdesc(div) {
        if (!this.canEdit) return;
        const draft = this.jobdescDraft[div.name];
        const title = draft.title.trim();
        if (!title) {
            this.jobdescError[div.name] = 'Tulis dulu apa yang harus dikerjakan.';
            return;
        }
        if (!Number.isInteger(draft.h)) {
            this.jobdescError[div.name] = 'Target harus berupa angka (hari sebelum Hari-H).';
            return;
        }
        this.jobdescError[div.name] = '';
        div.jobdesc.push({ id: ++nextId, title, h: draft.h, done: false });
        div.jobdesc.sort((a, b) => b.h - a.h); // earliest target first
        draft.title = '';
    },
    removeJobdesc(div, id) {
        if (!this.canEdit) return;
        div.jobdesc = div.jobdesc.filter((j) => j.id !== id);
    },

    scopeLabel(scope) {
        if (scope === 'pleno') return 'Rapat Pleno (semua panitia)';
        if (scope === 'koor') return 'Rapat Koordinator dan HOD/PJ';
        return `Rapat divisi ${scope.replace('div:', '')}`;
    },
    addRapat() {
        if (!this.canEdit) return;
        const d = this.rapatDraft;
        const day = Number(d.day);
        if (!d.time) {
            this.rapatError = 'Jam rapat wajib diisi.';
            return;
        }
        if (this.rapat.some((r) => r.scope === d.scope && r.day === day && r.time === d.time)) {
            this.rapatError = 'Jadwal rapat yang sama sudah ada.';
            return;
        }
        this.rapatError = '';
        this.rapat.push({ id: ++nextId, scope: d.scope, day, time: d.time, place: d.place.trim() });
        this.rapat.sort((a, b) => WEEK.indexOf(a.day) - WEEK.indexOf(b.day) || a.time.localeCompare(b.time));
    },
    removeRapat(id) {
        if (!this.canEdit) return;
        this.rapat = this.rapat.filter((r) => r.id !== id);
    },
    // Every meeting date from today up to Hari-H, soonest first.
    get meetings() {
        const out = [];
        for (let date = this.today; date <= this.proker.hariH; date = addDays(date, 1)) {
            const day = new Date(`${date}T00:00:00`).getDay();
            for (const r of this.rapat) if (r.day === day) out.push({ date, ...r });
        }
        return out.sort((a, b) => a.date.localeCompare(b.date) || a.time.localeCompare(b.time));
    },
    dayName(day) {
        return DAYS[day];
    },
});
