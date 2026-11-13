// Run with: node --test "tests/js/*.test.mjs"
import test from 'node:test';
import assert from 'node:assert/strict';
import prokerDetail from '../../resources/js/proker-detail.js';

// Hari-H is Saturday 2026-12-12; "today" is fixed so the dates do not drift.
const TODAY = '2026-11-12'; // a Thursday
const proker = () => ({
    nama: 'Techfest',
    hariH: '2026-12-12',
    lokasi: 'Auditorium UC',
    divisions: [
        { name: 'Event', koor: ['Felicia'], anggota: ['A', 'B'], jobdesc: [{ title: 'Master rundown', h: 30, done: true }, { title: 'Gladi bersih', h: 3, done: false }, { title: 'Brief talent', h: 40, done: false }] },
        { name: 'Tech', koor: [], anggota: [], jobdesc: [] },
    ],
    rapat: [{ id: 1, scope: 'pleno', day: 4, time: '19:00', place: 'Zoom' }],
});
const make = () => prokerDetail(proker(), TODAY);

test('the days left count down to Hari-H', () => {
    assert.equal(make().daysLeft, 30);
    assert.equal(prokerDetail({ ...proker(), hariH: '2026-11-10' }, TODAY).daysLeft, -2);
});

test('a jobdesc target counts back from Hari-H', () => {
    const f = make();
    assert.equal(f.dateFor(30), '2026-11-12');
    assert.equal(f.dateFor(0), '2026-12-12');
    assert.equal(f.dateFor(-2), '2026-12-14');
});

test('status: done, overdue or still to do', () => {
    const [done, soon, overdue] = make().divisions[0].jobdesc;
    const f = make();
    assert.equal(f.status(done), 'Selesai');
    assert.equal(f.status(soon), 'Belum');
    assert.equal(f.status(overdue), 'Terlambat'); // H-40 was 2026-11-02, before today
});

test('a target that falls on today is not overdue yet', () => {
    const f = make();
    assert.equal(f.status({ h: 30, done: false }), 'Belum');
});

test('progress per division and in total', () => {
    const f = make();
    assert.deepEqual(f.progress(f.divisions[0]), { done: 1, total: 3, pct: 33 });
    assert.deepEqual(f.progress(f.divisions[1]), { done: 0, total: 0, pct: 0 });
    assert.deepEqual(f.totals, { done: 1, total: 3, overdue: 1, koor: 1, anggota: 2 });
});

test('ticking a jobdesc moves the progress', () => {
    const f = make();
    f.divisions[0].jobdesc[1].done = true;
    assert.equal(f.totals.done, 2);
});

test('adding a jobdesc needs a title and a whole-number target, and keeps earliest target first', () => {
    const f = make();
    const event = f.divisions[0];
    f.addJobdesc(event);
    assert.match(f.jobdescError.Event, /Tulis dulu/);
    f.jobdescDraft.Event.title = 'Cek sound';
    f.jobdescDraft.Event.h = '';
    f.addJobdesc(event);
    assert.match(f.jobdescError.Event, /angka/);
    f.jobdescDraft.Event.h = 35;
    f.addJobdesc(event);
    assert.equal(f.jobdescError.Event, '');
    assert.deepEqual(event.jobdesc.map((j) => j.h), [40, 35, 30, 3]);
    assert.equal(f.jobdescDraft.Event.title, '');
});

test('a jobdesc can be removed', () => {
    const f = make();
    const event = f.divisions[0];
    f.removeJobdesc(event, event.jobdesc[0].id);
    assert.equal(event.jobdesc.length, 2);
});

test('weekly meetings expand to every date until Hari-H', () => {
    const f = make();
    // Thursdays from 2026-11-12 to 2026-12-12: Nov 12, 19, 26, Dec 3, 10
    assert.deepEqual(f.meetings.map((m) => m.date), ['2026-11-12', '2026-11-19', '2026-11-26', '2026-12-03', '2026-12-10']);
});

test('several schedules interleave by date and time', () => {
    const f = make();
    f.rapatDraft = { scope: 'koor', day: '1', time: '20:00', place: '' }; // Mondays
    f.addRapat();
    const dates = f.meetings.map((m) => `${m.date} ${m.scope}`);
    assert.equal(dates[0], '2026-11-12 pleno');
    assert.equal(dates[1], '2026-11-16 koor');
    assert.equal(f.meetings.length, 5 + 4);
});

test('a meeting on Hari-H itself is included, none after it', () => {
    const f = make();
    f.rapatDraft = { scope: 'pleno', day: '6', time: '08:00', place: '' }; // Saturday = Hari-H
    f.addRapat();
    const last = f.meetings.at(-1);
    assert.equal(last.date, '2026-12-12');
    assert.ok(f.meetings.every((m) => m.date <= '2026-12-12'));
});

test('no meetings once Hari-H has passed', () => {
    const f = prokerDetail({ ...proker(), hariH: '2026-11-01' }, TODAY);
    assert.deepEqual(f.meetings, []);
});

test('the same schedule cannot be added twice, and a time is required', () => {
    const f = make();
    f.rapatDraft = { scope: 'pleno', day: '4', time: '19:00', place: '' };
    f.addRapat();
    assert.match(f.rapatError, /sudah ada/);
    assert.equal(f.rapat.length, 1);
    f.rapatDraft.time = '';
    f.addRapat();
    assert.match(f.rapatError, /Jam rapat wajib/);
});

test('a division meeting is labelled with its division', () => {
    const f = make();
    assert.equal(f.scopeLabel('div:Tech'), 'Rapat divisi Tech');
    f.rapatDraft = { scope: 'div:Tech', day: '2', time: '18:00', place: 'Lab' };
    f.addRapat();
    assert.equal(f.rapat.length, 2);
    assert.equal(f.rapatError, '');
});

test('schedules are listed Monday first and can be removed', () => {
    const f = make();
    f.rapatDraft = { scope: 'koor', day: '0', time: '10:00', place: '' }; // Sunday goes last
    f.addRapat();
    f.rapatDraft = { scope: 'koor', day: '1', time: '20:00', place: '' };
    f.addRapat();
    assert.deepEqual(f.rapat.map((r) => r.day), [1, 4, 0]);
    f.removeRapat(f.rapat[0].id);
    assert.equal(f.rapat.length, 2);
});
