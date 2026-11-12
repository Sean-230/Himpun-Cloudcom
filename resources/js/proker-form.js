// State for the HOD "Buat proker" screen. Koordinator and anggota are optional on purpose:
// a division can be switched on with nobody assigned yet.
export default (members, divisionDefs) => ({
    members,
    divisions: divisionDefs.map((d) => ({ ...d, on: false, koor: [], anggota: [] })),
    form: { name: '', type: 'new', previous: '', hariH: '', lokasi: '', ketupel: '', pj: '' },
    custom: '',
    done: false,
    tried: false,
    notice: null,

    get active() {
        return this.divisions.filter((d) => d.on);
    },
    get totals() {
        const sum = (key) => this.active.reduce((n, d) => n + d[key].length, 0);
        return { divisi: this.active.length, koor: sum('koor'), anggota: sum('anggota') };
    },
    get today() {
        return new Date().toLocaleDateString('en-CA'); // YYYY-MM-DD in local time
    },
    get ketupelId() {
        return this.form.ketupel ? Number(this.form.ketupel) : null;
    },
    get pjId() {
        return this.form.pj ? Number(this.form.pj) : null;
    },

    // One person, one role in a proker: Ketupel, PJ, or a single koordinator/anggota slot.
    // (Across different prokers a person may still hold several roles.)
    holds(id) {
        const roles = [];
        if (id === this.ketupelId) roles.push('Ketupel');
        if (id === this.pjId) roles.push('PJ');
        for (const d of this.active) {
            if (d.koor.includes(id)) roles.push(`koordinator ${d.name}`);
            if (d.anggota.includes(id)) roles.push(`anggota ${d.name}`);
        }
        return roles;
    },
    // Roles a person holds other than the one they are being picked for.
    heldBesides(id, own) {
        return this.holds(id).filter((r) => r !== own);
    },
    get errors() {
        const e = {};
        const f = this.form;
        if (!f.name.trim()) e.name = 'Nama proker wajib diisi.';
        if (!f.hariH) e.hariH = 'Hari-H wajib diisi.';
        else if (f.hariH < this.today) e.hariH = 'Hari-H tidak boleh sebelum hari ini.';
        if (f.type === 'repeat' && !f.previous) e.previous = 'Pilih proker periode lalu yang diulang.';
        if (!this.active.length) e.divisions = 'Pilih minimal satu divisi.';
        for (const m of this.members) {
            const held = this.holds(m.id);
            if (held.length < 2) continue;
            const key = held.includes('Ketupel') ? 'ketupel' : held.includes('PJ') ? 'pj' : 'people';
            e[key] ??= held.includes('Ketupel') && held.includes('PJ')
                ? 'Ketupel dan PJ tidak boleh orang yang sama.'
                : `${m.name} memegang lebih dari satu peran (${held.join(', ')}). Satu orang hanya boleh satu peran di satu proker.`;
        }
        return e;
    },
    get canSubmit() {
        return Object.keys(this.errors).length === 0;
    },
    // Messages only show after the first attempt to submit, so an empty form is not shouting.
    err(key) {
        return this.tried ? this.errors[key] || '' : '';
    },

    person(id) {
        return this.members.find((m) => m.id === id);
    },
    // Everyone not already in this list. Picking someone who holds another role moves them.
    available(div, list) {
        return this.members.filter((m) => !div[list].includes(m.id));
    },
    // Give a person exactly one role: take them out of every other role in this proker and say so.
    // target is 'ketupel', 'pj' or { div, list }.
    move(id, target) {
        const from = [];
        if (target !== 'ketupel' && this.ketupelId === id) {
            this.form.ketupel = '';
            from.push('Ketupel');
        }
        if (target !== 'pj' && this.pjId === id) {
            this.form.pj = '';
            from.push('PJ');
        }
        for (const d of this.divisions) {
            for (const list of ['koor', 'anggota']) {
                if (target.div === d && target.list === list) continue;
                if (d[list].includes(id)) {
                    d[list] = d[list].filter((x) => x !== id);
                    from.push(`${list === 'koor' ? 'koordinator' : 'anggota'} ${d.name}`);
                }
            }
        }
        const role = (list, d) => `${list === 'koor' ? 'koordinator' : 'anggota'} ${d.name}`;
        const to = target === 'ketupel' ? 'Ketupel' : target === 'pj' ? 'PJ' : role(target.list, target.div);
        const key = typeof target === 'string' ? target : target.div.name;
        this.notice = from.length ? { key, msg: `${this.person(id).name} dipindah dari ${from.join(' dan ')} ke ${to}.` } : null;
    },
    claim(kind) {
        const id = kind === 'ketupel' ? this.ketupelId : this.pjId;
        if (id) this.move(id, kind);
    },
    noticeAt(key) {
        return this.notice?.key === key ? this.notice.msg : '';
    },
    add(div, list, value) {
        const id = Number(value);
        if (!id || div[list].includes(id)) return;
        this.move(id, { div, list });
        div[list].push(id);
    },
    remove(div, list, id) {
        div[list] = div[list].filter((x) => x !== id);
        this.notice = null;
    },
    // Open roles across prokers, counting this proker once.
    roles(id) {
        return this.person(id).roles + (this.holds(id).length ? 1 : 0);
    },
    addCustom() {
        const name = this.custom.trim();
        if (!name) return;
        const existing = this.divisions.find((d) => d.name.toLowerCase() === name.toLowerCase());
        if (existing) existing.on = true;
        else this.divisions.push({ name, desc: 'Divisi tambahan', on: true, koor: [], anggota: [] });
        this.custom = '';
    },

    // Stable pale tint per person: identity, not data.
    hue(name) {
        let h = 0;
        for (const c of name) h = (h * 31 + c.charCodeAt(0)) % 360;
        return h;
    },
    initials(name) {
        return name.split(' ').slice(0, 2).map((w) => w[0]).join('');
    },
    avatarStyle(name) {
        const h = this.hue(name);
        return `background: hsl(${h} 55% 92%); color: hsl(${h} 40% 28%)`;
    },

    fmtDate(iso) {
        return iso ? new Date(iso).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '';
    },
    names(ids) {
        return ids.map((id) => this.person(id).name).join(', ');
    },

    submit() {
        this.tried = true;
        if (this.canSubmit) {
            this.done = true;
        } else {
            this.$nextTick(() => document.querySelector('[data-error]:not([style*="none"])')?.scrollIntoView({ block: 'center', behavior: 'smooth' }));
        }
    },
});
