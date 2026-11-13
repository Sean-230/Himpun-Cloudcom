// Run with: node --test "tests/js/*.test.mjs"
import test from 'node:test';
import assert from 'node:assert/strict';
import prokerForm from '../../resources/js/proker-form.js';

const members = [
    { id: 1, name: 'Clarissa Tan', divisi: 'HOD · Ketua', hod: true, roles: 0 },
    { id: 2, name: 'Michelle Wijaya', divisi: 'HOD · Wakil Ketua', hod: true, roles: 0 },
    { id: 3, name: 'Rafael Santoso', divisi: 'Event', hod: false, roles: 0 },
    { id: 4, name: 'Timothy Lee', divisi: 'Tech', hod: false, roles: 2 },
    { id: 5, name: 'Felicia Hartono', divisi: 'Event', hod: false, roles: 0 },
];
const defs = [{ name: 'Event', desc: '', jobdesc: [{ title: 'Master rundown', h: 30 }] }, { name: 'Tech', desc: '' }];

// A valid form to start from; each test breaks one thing.
function valid() {
    const f = prokerForm(members, defs);
    f.$nextTick = () => {};
    f.form.name = 'Techfest';
    f.form.hariH = '2999-01-01';
    f.divisions[0].on = true;
    return f;
}
const event = (f) => f.divisions[0];
const tech = (f) => f.divisions[1];
const pick = (f, kind, id) => {
    f.form[kind] = String(id);
    f.claim(kind);
};

test('a valid form can be submitted, with nobody assigned', () => {
    const f = valid();
    assert.deepEqual(f.errors, {});
    f.submit();
    assert.equal(f.done, true);
});

test('koordinator and anggota are optional', () => {
    assert.deepEqual(valid().totals, { divisi: 1, koor: 0, anggota: 0, jobdesc: 1 });
});

test('name, Hari-H and at least one division are required', () => {
    const f = prokerForm(members, defs);
    assert.deepEqual(Object.keys(f.errors).sort(), ['divisions', 'hariH', 'name']);
});

test('Hari-H cannot be in the past', () => {
    const f = valid();
    f.form.hariH = '2000-01-01';
    assert.match(f.errors.hariH, /sebelum hari ini/);
});

test('a repeat proker needs the previous one', () => {
    const f = valid();
    f.form.type = 'repeat';
    assert.ok(f.errors.previous);
    f.form.previous = 'Techfest UC 2025';
    assert.equal(f.errors.previous, undefined);
});

test('a koordinator made PJ is taken out of the division', () => {
    const f = valid();
    f.add(event(f), 'koor', 2);
    pick(f, 'pj', 2);
    assert.deepEqual(event(f).koor, []);
    assert.equal(f.form.pj, '2');
    assert.match(f.noticeAt('pj'), /Michelle Wijaya dipindah dari koordinator Event ke PJ/);
    assert.deepEqual(f.holds(2), ['PJ']);
});

test('an anggota made Ketupel is taken out of the division', () => {
    const f = valid();
    f.add(event(f), 'anggota', 3);
    pick(f, 'ketupel', 3);
    assert.deepEqual(event(f).anggota, []);
    assert.deepEqual(f.holds(3), ['Ketupel']);
});

test('making the PJ the Ketupel clears the PJ, and the other way round', () => {
    const f = valid();
    pick(f, 'pj', 1);
    pick(f, 'ketupel', 1);
    assert.equal(f.form.pj, '');
    assert.equal(f.form.ketupel, '1');
    pick(f, 'pj', 1);
    assert.equal(f.form.ketupel, '');
    assert.match(f.noticeAt('pj'), /dari Ketupel ke PJ/);
});

test('adding the Ketupel or PJ to a division moves them out of that role', () => {
    const f = valid();
    pick(f, 'ketupel', 3);
    pick(f, 'pj', 2);
    f.add(event(f), 'koor', 3);
    f.add(event(f), 'anggota', 2);
    assert.equal(f.form.ketupel, '');
    assert.equal(f.form.pj, '');
    assert.deepEqual(event(f).koor, [3]);
    assert.deepEqual(event(f).anggota, [2]);
});

test('adding someone to another division moves them, never copies them', () => {
    const f = valid();
    tech(f).on = true;
    f.add(event(f), 'koor', 5);
    f.add(tech(f), 'anggota', 5);
    assert.deepEqual(event(f).koor, []);
    assert.deepEqual(tech(f).anggota, [5]);
    assert.equal(f.holds(5).length, 1);
    assert.match(f.noticeAt('Tech'), /dari koordinator Event ke anggota Tech/);
});

test('moving a koordinator to anggota in the same division works', () => {
    const f = valid();
    f.add(event(f), 'koor', 5);
    f.add(event(f), 'anggota', 5);
    assert.deepEqual([event(f).koor, event(f).anggota], [[], [5]]);
});

test('a person already in a list is not offered, and cannot be added twice', () => {
    const f = valid();
    f.add(event(f), 'koor', 5);
    assert.equal(f.available(event(f), 'koor').some((m) => m.id === 5), false);
    f.add(event(f), 'koor', 5);
    assert.deepEqual(event(f).koor, [5]);
});

test('someone left in a switched-off division is cleaned out when given another role', () => {
    const f = valid();
    f.add(event(f), 'anggota', 4);
    event(f).on = false;
    tech(f).on = true;
    pick(f, 'ketupel', 4);
    assert.deepEqual(event(f).anggota, []);
    event(f).on = true;
    assert.deepEqual(f.errors, {});
});

test('moving nobody leaves no notice, and removing a chip clears it', () => {
    const f = valid();
    f.add(event(f), 'koor', 5);
    assert.equal(f.notice, null);
    f.add(tech(f), 'koor', 5);
    assert.ok(f.notice);
    f.remove(tech(f), 'koor', 5);
    assert.equal(f.notice, null);
});

test('the validation still catches a clash that gets past the UI', () => {
    const f = valid();
    event(f).koor.push(2);
    f.form.pj = '2';
    assert.match(f.errors.pj, /Michelle Wijaya/);
    f.form.pj = '';
    f.form.ketupel = '2';
    f.form.pj = '2';
    assert.equal(f.errors.ketupel ?? f.errors.pj, 'Ketupel dan PJ tidak boleh orang yang sama.');
});

test('open roles across prokers count this proker once', () => {
    const f = valid();
    assert.equal(f.roles(4), 2);
    f.add(event(f), 'koor', 4);
    assert.equal(f.roles(4), 3);
});

test('a custom division is added once; repeating it switches the existing one on', () => {
    const f = valid();
    f.custom = 'Konsumsi';
    f.addCustom();
    f.custom = 'konsumsi';
    f.addCustom();
    assert.equal(f.divisions.filter((d) => d.name.toLowerCase() === 'konsumsi').length, 1);
    tech(f).on = false;
    f.custom = 'Tech';
    f.addCustom();
    assert.equal(tech(f).on, true);
});

test('errors stay hidden until the first submit attempt', () => {
    const f = prokerForm(members, defs);
    f.$nextTick = () => {};
    assert.equal(f.err('name'), '');
    f.submit();
    assert.ok(f.err('name'));
    assert.equal(f.done, false);
});

test('each division starts with its default jobdesc, copied per form', () => {
    const a = valid();
    const b = valid();
    assert.deepEqual(event(a).jobdesc.map((j) => [j.title, j.h]), [['Master rundown', 30]]);
    event(a).jobdesc[0].title = 'Ubah';
    assert.equal(event(b).jobdesc[0].title, 'Master rundown');
    assert.deepEqual(tech(a).jobdesc, []);
});

test('jobdesc can be added and removed', () => {
    const f = valid();
    f.addJobdesc(event(f));
    assert.equal(event(f).jobdesc.length, 2);
    f.removeJobdesc(event(f), event(f).jobdesc[0].id);
    assert.equal(event(f).jobdesc.length, 1);
});

test('a blank jobdesc title is an error, but only in a division that is switched on', () => {
    const f = valid();
    f.addJobdesc(tech(f));
    assert.equal(f.errors.jobdesc, undefined);
    f.addJobdesc(event(f));
    assert.match(f.errors.jobdesc, /judulnya masih kosong/);
    event(f).jobdesc[1].title = 'Gladi bersih';
    assert.equal(f.errors.jobdesc, undefined);
});

test('a jobdesc target must be a whole number', () => {
    const f = valid();
    event(f).jobdesc[0].h = '';
    assert.match(f.errors.jobdesc, /angka/);
});

test('the target date counts back from Hari-H', () => {
    const f = valid();
    f.form.hariH = '2027-03-18';
    assert.equal(f.targetDate(30), '16 Feb');
    assert.equal(f.targetDate(0), '18 Mar');
    assert.equal(f.targetDate(-2), '20 Mar');
    assert.equal(f.targetLabel(30), 'H-30');
    assert.equal(f.targetLabel(0), 'Hari-H');
    assert.equal(f.targetLabel(-2), 'H+2');
    f.form.hariH = '';
    assert.equal(f.targetDate(30), '');
});
