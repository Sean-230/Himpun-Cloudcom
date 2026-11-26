# Himpun: per-screen prompts for Figma Make

How to use: paste **Prompt 0** once (it sets the design system). Then paste **Prompt 1, 2, 3...** one at a time, waiting for each result and checking it before the next. Each screen prompt repeats the data it needs, so it still works if Figma Make forgets earlier turns. If a screen drifts from the style, paste **Prompt 99** (consistency fix).

UI text must be Indonesian. The prompts themselves are English.

---

## Prompt 0: foundation (paste first, once)

```
Create a web app called "Himpun": an ERP for Indonesian university student organisations (first customer: Student Union IMT, Universitas Ciputra). It is a calm work tool for students running event committees, not a marketing site. Build app screens only. All UI text is Indonesian. Desktop frames 1440px, phone frames 390px. Make everything clickable.

SHELL: one tinted frame (#F6FAFD) holds a left sidebar (232px) and a 48px top bar with NO divider between them, forming an L. The page is a white sheet inset 8px from the window's right and bottom edges, 16px radius, 1px edge #E3EAF1, no shadow, 24px inner padding. Top bar: centred search 548x32 with a "⌘K" hint; on the right a role select "Tampilkan sebagai" (Anggota, Koordinator, HOD/PJ, Ketupel, Dosen Pembina), a bell, an account button. Sidebar top: a simple monogram "H" (two vertical bars, navy) + "Himpun", then the switcher "SU IMT · 2025/2026". Nav (grey, sentence case, 16px line icons, active item = neutral grey pill): Ringkasan, Proker, Panitia saya, Anggota, Divisi & jobdesc, Persetujuan (grey count badge), Anggaran, Oprec, Pulse, Dokumen, Pengaturan.

TOKENS: ink #0A1931, muted #5B6B80, border #E3EAF1, accent #1A3D63 (the ONE primary button per screen and active tab underline only), data hue "actual" #2F6EA5, data hue "planned" #B3CFE5. Status as pale-tint pills with dark text: good #1F7A4D, warning #A15C07, danger #B42318. Colour covers under 5% of any screen.

TYPE: IBM Plex Sans 400/500/600; IBM Plex Mono only for NIM/IDs. Title 22, section 16, KPI value 28 (weight 400), body 14, small 12. Sentence case, no letter-spaced capitals, tabular numbers, numbers right-aligned in tables.

COMPONENTS: inputs 40px, 1px border, 10px radius, label 14/500 above. Tables: no zebra, no header fill, grey 12-13px headers, 1px row rules, 48-56px rows, one status pill + one kebab per row. KPI strip: ONE container split into equal cells by 1px dividers, label 14 grey, value 28, caption 12; no icons, no sparklines. People = round 28px initial avatars in pale personal tints. Charts minimal, no legend for a single series.

SIGNATURE ELEMENT "ChainStepper": a small horizontal stepper of 4 plain labelled nodes joined by a line: Anggota → Koordinator → HOD/PJ → Ketupel. Done steps in ink, current step with an accent underline, future steps grey. It shows where an approval/task is stuck. Build it as a reusable component.

NEVER: gradients, glows, blur/glass, shadows on cards, emoji, icon-topped cards, rainbow charts, coloured left borders, zebra tables, stock photos, lorem ipsum.

Create reusable components named in English: Sidebar, TopBar, KpiStrip, DataTable, StatusPill, ChainStepper, TaskCard, Modal, SidePanel. Use variables for the tokens. Start by building only the shell with an empty page and the nav working.
```

---

## Prompt 1: Masuk (login)

```
Using the same Himpun design system, add the login screen. No sidebar. A centred 400px white sheet on the tinted #F6FAFD frame: Himpun monogram, title "Masuk ke Himpun", fields "NIM atau email" and "Kata sandi", one primary button "Masuk", text link "Lupa kata sandi?". Under the sheet, a quiet line "Mau jadi panitia? Lihat lowongan terbuka" linking to the public Oprec page. After Masuk, go to Ringkasan.
```

## Prompt 2: Ringkasan (overview)

```
Add the "Ringkasan" screen (default after login). Title "Ringkasan", grey line "SU IMT · Periode 2025/2026", one primary button "Usulkan proker" on the right.

Scope row: tabs "Semua proker · Berjalan · Selesai" and a date-range select.
KPI container, 4 cells: "Proker aktif 3" · "Anggota aktif 42" (caption "8 peserta Pulse") · "Persetujuan menunggu 5" · "Tugas lewat tenggat 4". Captions say "dibanding periode lalu".
Layout: main column + right rail 384px, 24px gap.
Main column, in order:
1) The largest card: "Anggaran rencana vs realisasi per proker", grouped bars (planned #B3CFE5, actual #2F6EA5), Rupiah values.
2) Card "Beban kerja anggota lintas proker": matrix rows = members, columns = prokers, cells = role letter (K = Ketupel, Ko = Koordinator, A = Anggota). Flag anyone with 3+ active roles with warning text.
Right rail (headings outside cards): "Menunggu persetujuanmu" — 3 rows each with a ChainStepper and an outlined "Lihat semua"; "Segera" — next deadlines and the Hari-H countdown for Techfest UC 2026 (12 Des 2026).
Phone: rail drops below, KPI becomes 2x2.
```

## Prompt 3: Proker (list)

```
Add the "Proker" list screen. Title "Proker", primary "Usulkan proker". Underline tabs: "Usulan · Pembentukan panitia · Berjalan · LPJ · Arsip". Toolbar: search, periode filter, count "4 proker".
Table columns: Nama proker (widest; muted tag "Ulangi" or "Baru" after the name), Ketupel, PJ, Hari-H, Status (pill), Anggaran terpakai (right-aligned, % and Rupiah), kebab.
Rows:
- Techfest UC 2026 · Ulangi · Rafael Santoso · Michelle Wijaya · 12 Des 2026 · Berjalan · 55% (Rp 26.850.000)
- IMT Orientation Camp · Ulangi · Kevin Pratama · Clarissa Tan · 20 Des 2026 · Pembentukan panitia · 0%
- National Hackathon UC · Baru · "Belum ditunjuk" (muted) · Darren Lim · 18 Jan 2027 · Usulan · 0%
- IMT Class Meeting 2025 · Jessica Lau · Natalia Hendra · selesai · Arsip
Below the table a section "Warisan periode lalu": archived prokers, each with three outlined buttons "Ulangi", "Arsipkan", "Bubarkan". Row click opens the proker detail.
```

## Prompt 4: Detail proker (Ringkasan + Panitia tabs)

```
Add the proker detail screen for "Techfest UC 2026". Header: name (22px), the ChainStepper (current step: HOD/PJ), a status pill "Berjalan", "Hari-H 12 Des 2026 · 25 hari lagi", outlined button "Ubah". Tabs: Ringkasan · Panitia · Tugas · Anggaran · Oprec · Dokumen.

Tab "Ringkasan": four facts (Ketupel Rafael Santoso, PJ Michelle Wijaya, Hari-H, Lokasi Auditorium UC), a progress line "12 dari 20 tugas selesai", a short activity feed.

Tab "Panitia": top block Ketupel + HOD (Clarissa Tan, Michelle Wijaya, Darren Lim, Natalia Hendra) + PJ. Then one section per proker division: Event, Properties, People Relation, Marketing, PDD Dokum, PDD Design, Tech. Each shows its Koordinator and 3-5 anggota (avatar, name, 13-digit NIM in mono, "lapor ke ..."), and a headcount like "4 dari 5". Koordinators: Tech Andreas Gunawan, Event Felicia Hartono, PDD Design Clarissa Putri, Properties Bryan Santoso. Buttons: outlined "Tambah divisi panitia", primary "Tugaskan anggota" (opens Prompt 5's modal).
```

## Prompt 5: Modal "Tugaskan anggota"

```
Add a modal "Tugaskan anggota" opened from the Panitia tab. Fields: Anggota (search by name/NIM), Divisi panitia (select), Peran (Koordinator / Anggota), Lapor ke (auto-filled, read-only). When the chosen person already holds 2 or more active roles, show a warning strip: "Andreas sudah memegang 2 peran aktif di proker lain." Buttons: text "Batal", primary "Tugaskan". Submitting adds the person to the chosen division and closes the modal.
```

## Prompt 6: Tugas (board + side panel)

```
Add the "Tugas" tab of the proker detail: a board with columns "Akan dikerjakan · Dikerjakan · Ditinjau · Selesai". Cards show title, a division tag, due date (danger colour only when overdue), assignee avatars. Tasks: "Deploy landing page registrasi peserta" (Tech, Selesai) · "Sistem QR check-in & e-certificate" (Tech, Dikerjakan, tenggat 5 Des) · "Master rundown & brief pembicara" (Event, Ditinjau) · "Booking Auditorium UC & izin alat" (Properties, lewat tenggat) · "Key visual & desain lanyard" (PDD Design, Selesai). Clicking a card opens a right SidePanel with description, assignees, comments and the ChainStepper showing the current approval step. Buttons depend on the role selected in "Tampilkan sebagai": Anggota sees "Ajukan tinjauan"; Koordinator/HOD/PJ/Ketupel see "Setujui" and "Kembalikan". Approving moves the stepper one step; "Kembalikan" shows a reviewer note field.
```

## Prompt 7: Persetujuan

```
Add the "Persetujuan" screen (approvals inbox). Tabs "Menunggu · Disetujui · Dikembalikan". A list of items awaiting the viewer: tugas, dokumen, pengeluaran anggaran, proker usulan. Each row: item title, proker, submitter (avatar + name), the ChainStepper, age ("2 hari"), outlined buttons "Setujui" and "Kembalikan". Show 5 pending items. Under the title, one muted line: "Persetujuan berjalan berurutan: Koordinator → HOD/PJ → Ketupel." Approving removes the row and updates the sidebar badge count.
```

## Prompt 8: Anggaran

```
Add the "Anggaran" tab/screen for Techfest UC 2026. Segmented switch "Rencana (proposal) / Realisasi (LPJ)". KPI container, 4 cells: "Rencana Rp 48.500.000" · "Realisasi Rp 26.850.000" (caption "55% terpakai") · "Sisa kas Rp 5.750.000" · "Pemasukan Rp 32.600.000".
Left: table "Divisi / Rencana / Realisasi / Selisih" (numbers right-aligned, selisih in good or danger text colour):
Event 14.000.000 / 6.800.000; Properties 11.500.000 / 9.200.000 (flag warning "80% terpakai"); Marketing 6.000.000 / 1.500.000; PDD Design 4.000.000 / 3.600.000; PDD Dokum 5.000.000 / 2.100.000; Tech 3.500.000 / 2.400.000; People Relation 4.500.000 / 1.250.000; total row 48.500.000 / 26.850.000.
Right: list of entries (date, description, amount, receipt link, submitted by). Pemasukan: sponsor 20.000.000 and tiket 12.600.000 (84 tiket x 150.000). Buttons: primary "Catat pengeluaran" (opens a modal: kategori, divisi, jumlah, tanggal, upload struk), outlined "Catat pemasukan".
```

## Prompt 9: Oprec (internal)

```
Add the "Oprec" screen. List of vacancies: title, proker, divisi, kuota, pelamar, tanggal tutup, status (Draft/Dibuka/Ditutup). Open "Staff Tech — Techfest UC 2026" (kuota 3, 11 pelamar) to show a pipeline board with columns "Melamar (4) · Seleksi berkas (3) · Wawancara (2) · Diterima (1) · Ditolak (1)". Applicant cards: name, NIM (mono), prodi, a short note. On an accepted card show a button "Terima & masukkan ke panitia"; clicking it adds the person to the Panitia tab. Primary button "Buat lowongan".
```

## Prompt 10: Oprec (halaman publik)

```
Add the public Oprec application page (no sidebar, no login). Centred 720px column on the tinted frame: proker name "Techfest UC 2026", division "Staff Tech", kuota 3, requirements list, deadline. Form: Nama, NIM, Program studi, Angkatan, No. WhatsApp, Motivasi (textarea), upload CV. One primary button "Kirim lamaran". Success state replaces the form: "Lamaran terkirim. Kami hubungi lewat WhatsApp."
```

## Prompt 11: Pulse

```
Add the "Pulse" screen (internship pipeline). Header "Pulse Angkatan 5 · 8 peserta" with a KPI container: "Aktif 6 · Lulus 1 · Mundur 1". Table of interns: avatar + name, NIM (mono), Mentor, Kehadiran (%), Skor (0-100), Status pill. Buttons: outlined "Pasangkan mentor" (modal) and a small evaluation form (skor 0-100 + catatan). Encouraging, plain tone.
```

## Prompt 12: Anggota

```
Add the "Anggota" directory. Search plus filters Divisi and Status (Aktif, Pulse, Alumni). Table: Nama (avatar), NIM (mono), Divisi, Jabatan, Proker aktif (count), Status. Clicking a row opens a SidePanel with the person's current roles across prokers and their workload (e.g. Andreas Gunawan: Koordinator Tech in Techfest UC 2026, Anggota PDD Design in IMT Orientation Camp), plus a warning if they hold 3 or more active roles.
```

## Prompt 13: Divisi & jobdesc

```
Add the "Divisi & jobdesc" screen. Top block: the standing HOD roles (Ketua Clarissa Tan, Wakil Michelle Wijaya, Sekretaris Darren Lim, Bendahara Natalia Hendra) and Dosen Pembina Dr. Hendra Kusuma. Below: six division sections (Tech, PDD Design, PDD Dokum, Event, Social Relation, Properties), each with its Koordinator, member count and a list of jobdesc (title + level Koordinator/Staff). Outlined button "Tambah jobdesc" opens a modal (judul, divisi, level, deskripsi).
```

## Prompt 14: Dokumen + Pengaturan

```
Add two screens. "Dokumen": table of proposals and LPJ per proker (type, version, status Draft/Diajukan/Disetujui, uploader, date, kebab) with an upload modal. "Pengaturan": two-column settings, label column ~450px with a 16/500 title and grey description, beside a #FAFBFD panel (16px radius, no border); sections "Organisasi" (name, type, fakultas), "Periode" (create/close a term, set active), "Peran & akses" (roles x permissions as toggles), "Notifikasi"; sections separated by a full-width 1px rule.
```

## Prompt 15: Phone pass

```
Create 390px phone versions of Ringkasan, Proker, Detail proker, Tugas, Persetujuan and Anggaran. The tinted frame disappears: the sheet becomes the screen with a 48px top bar (menu button, title, one action). The sidebar opens as a full-height sheet. KPI strip becomes a 2x2 grid; the right rail drops below the main column; tables become two-line rows (title + key number on line 1, secondary fields in grey on line 2, kebab at right); tabs scroll horizontally; the task board becomes one column with a column switcher; touch targets at least 44px.
```

## Prompt 99: consistency fix (use when a screen drifts)

```
Check this screen against the Himpun system and fix any drift: no gradients, shadows, glows, emoji, icon-topped cards, zebra rows or coloured card borders; only one filled primary button; colour only on data and status pills; numbers right-aligned in tabular figures; Indonesian text only; sentence case; sheet inset 8px with 16px radius and no divider between sidebar and top bar. Reuse existing components instead of redrawing.
```
