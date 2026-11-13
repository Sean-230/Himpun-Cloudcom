// Dates travel as YYYY-MM-DD strings and are always read in local time.
export const addDays = (iso, n) => {
    const d = new Date(`${iso}T00:00:00`);
    d.setDate(d.getDate() + n);
    return d.toLocaleDateString('en-CA');
};

export const fmtShort = (iso) => new Date(`${iso}T00:00:00`).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });

export const fmtLong = (iso) => new Date(`${iso}T00:00:00`).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });

// Target of a jobdesc, in days before Hari-H (negative = after): 30 -> "H-30", 0 -> "Hari-H", -2 -> "H+2".
export const targetLabel = (h) => (h > 0 ? `H-${h}` : h === 0 ? 'Hari-H' : `H+${-h}`);
