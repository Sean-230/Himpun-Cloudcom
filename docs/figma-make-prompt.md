# Prompt for Figma Make: Himpun (student union ERP)

Paste everything below the line into Figma Make. It is written in English because Figma Make follows English best; every word shown **inside the UI** must be Indonesian (labels, buttons, empty states, sample data), exactly as written in the sections below.

---

## 0. What you are building

Design a complete, clickable web app called **Himpun**: an ERP for Indonesian university student organisations. First customer: **Student Union IMT** (Himpunan Mahasiswa) at Universitas Ciputra; later other organisations (Student Council, Mentoring Department, Student Representative Board, UKM) and small work programs.

Users are students running a committee. They are not office workers: the app must feel calm, quick and obvious, and must teach nobody anything. It is a **work tool**, not a marketing site. Build the app screens only, no landing page.

Output as responsive web: desktop frames at **1440 px** wide and phone frames at **390 px**. Make the prototype navigable (sidebar links, tabs, row clicks, modals, role switcher all work).

## 1. How the organisation works (the model behind every screen)

- **Dosen Pembina**: faculty advisor. Advises, validates, resolves deadlocks. Sees everything, rarely acts.
- **HOD** (executive board): **Ketua (President), Wakil Ketua (Vice President), Sekretaris, Bendahara**. They govern the whole union and are "guardians" of every event.
- **Divisi tetap** (standing divisions): Tech, PDD Design, PDD Dokum, Event, Social Relation, Properties. Each has a **Koordinator** and **Staff**. Each division has **jobdesc** (job descriptions).
- **Proker** (program kerja, work program): a big event or campaign such as a hackathon or orientation camp. A proker is *repeated* from last term, *new*, or *disbanded/archived*. After approval a temporary **panitia** (committee) is formed.
- **Panitia structure per proker**: **Ketupel** (Ketua Pelaksana, project head) → **HOD + Penanggung Jawab (PJ)** oversight → **Koordinator** per proker-division → **Anggota**. The Ketupel chooses which divisions this event needs (Event, Inventory/Properties, People Relation, Marketing, PDD Dokum, PDD Design, Tech).
- **One person can hold many roles across many prokers** (Koordinator Tech in Proker A, Staff PDD Design in Proker B). Permissions are **per proker**.
- **Open Recruitment (Oprec)**: if a panitia lacks people, it opens vacancies to all campus students, screens and interviews, then inducts them.
- **Pulse**: an internship/talent pipeline of junior students, each paired with a mentor, evaluated before full induction.
- **Strict reporting chain (no skipping levels)**: `Anggota → Koordinator → HOD/PJ → Ketupel`. Approvals and task sign-offs follow it step by step. Dosen Pembina is consulted only for blockers.
- Work is organised by **periode** (term, e.g. 2025/2026): last term's prokers become the history pool.
- Each proker has a **budget**: *rencana* (planned, in the proposal) versus *realisasi* (actual, in the LPJ accountability report).

## 2. The one idea of this product's look

Himpun's own element is **the chain**: a small horizontal stepper `Anggota → Koordinator → HOD/PJ → Ketupel` that shows *where something is stuck right now*. It appears on every approval, every task in review and every proker header. Nothing else in the UI is decorative. Draw it as four plain labelled nodes joined by a line; done steps in ink, the current step with the accent underline, future steps in grey.

## 3. Visual system (follow exactly)

**Style:** quiet, flat, dense but breathable. Colour goes on **data**, not on chrome.

- **Shell ("tinted frame, white sheet"):** one tinted frame (`#F6FAFD`) holds the **left sidebar (232 px)** and the **48 px top bar** with **no divider between them**, forming an L. The page is a **white sheet** inset **8 px** from the window's right and bottom edges, **16 px corner radius**, **1 px edge `#E3EAF1`**, **no shadow**. Content padding inside the sheet: 24 px.
- **Top bar:** centred search field 548 × 32 with a `⌘K` hint; on the right a **role switcher** (see §6), notifications, and the account button.
- **Sidebar:** product mark + name **Himpun** at the top (draw a simple monogram "H" built from two vertical bars, in navy, not a black square), then the organisation/periode switcher ("SU IMT · 2025/2026"). Nav items in sentence case, grey; the active item is a **neutral grey pill**, never a coloured block. Line icons, 16 px, no coloured backgrounds on icons.
- **Colour tokens**
  - ink `#0A1931`, muted `#5B6B80`, border `#E3EAF1`, sheet `#FFFFFF`, frame `#F6FAFD`
  - accent (primary button, active tab underline): `#1A3D63`
  - data hue 1 (actual / current): `#2F6EA5`; data hue 2 (planned): `#B3CFE5`
  - status: good `#1F7A4D`, warning `#A15C07`, danger `#B42318`, each as a **pale-tint pill with dark text** (never solid fills)
  - Colour should cover under **5 % of any screen**. At most **one filled primary button per screen**; every other button is outlined or text.
- **Typography:** **IBM Plex Sans** (400/500/600), **IBM Plex Mono** only for NIM and IDs. Sizes: page title 22, section heading 16, KPI value 28 (weight 400), body 14, small 12. Sentence case everywhere, no letter-spaced capitals. `tabular-nums` on all numbers, **right-align numbers** in tables.
- **Forms:** 40 px inputs, 1 px `#E3EAF1` border, 10 px radius, label 14/500 above (8 px gap), 16 px between fields.
- **Tables:** no zebra stripes, no header fill, grey 12-13 px sentence-case headers, 1 px row rules, 48-56 px rows, one status pill and one kebab menu per row. Drop any column empty in every row.
- **KPI strip:** one container split into 3-4 equal cells by 1 px dividers; each cell = 14 px grey label, 28 px value, 12 px caption naming the baseline ("dibanding periode lalu"). **No icons, no sparklines, no coloured circles.**
- **Charts:** minimal. No legend when there is one series. 3-5 faint gridlines. Colour only on the data.
- **People:** each person gets a round initial avatar (28 px) in a pale tint of a stable personal hue (hues at least 60° apart for people shown next to each other). Never stock photos.
- **Empty states** keep their frame and say what to do ("Belum ada tugas. Buat tugas pertama untuk divisi ini.") with one outlined button.

**Never include:** gradients, glows, glass/blur, shadows on resting cards, emoji, gradient blobs, "Welcome back 👋", icon-topped feature cards, rainbow charts, coloured left-border cards, zebra tables, fake photos, lorem ipsum, "Acme".

## 4. Screens to build (desktop 1440 + phone 390)

Sidebar navigation (Indonesian labels, in this order): **Ringkasan · Proker · Panitia saya · Anggota · Divisi & jobdesc · Persetujuan · Anggaran · Oprec · Pulse · Dokumen · Pengaturan**. Show a count badge (grey) only on *Persetujuan*.

### 4.1 Masuk (login)
Centred 400 px sheet on the tinted frame: Himpun mark, "Masuk ke Himpun", fields **NIM atau email**, **Kata sandi**, one primary button **Masuk**, text link "Lupa kata sandi?". Below, a quiet line "Mau jadi panitia? Lihat lowongan terbuka" linking to the public Oprec page (§4.9).

### 4.2 Ringkasan (overview; the default screen)
Title **Ringkasan** with the grey line "SU IMT · Periode 2025/2026" and one primary action **Usulkan proker**.
- Scope row: tabs **Semua proker · Berjalan · Selesai** and a date-range select.
- KPI container (4 cells): **Proker aktif 3** · **Anggota aktif 42** (caption "8 peserta Pulse") · **Persetujuan menunggu 5** · **Tugas lewat tenggat 4**.
- Main chart card (largest thing on screen): **"Anggaran rencana vs realisasi per proker"**, grouped bars, planned `#B3CFE5` and actual `#2F6EA5`, values in Rupiah.
- Second card: **"Beban kerja anggota lintas proker"**, a compact matrix (rows = members, columns = prokers, cells = role letter K/Ko/A) with a flag on anyone holding 3+ active roles.
- Right rail (384 px): headings outside cards. **"Menunggu persetujuanmu"** (3-5 rows, each with the chain stepper) with an outlined **Lihat semua**; **"Segera"** (next deadlines and the countdown to each Hari-H).

### 4.3 Proker (list)
Tabs: **Usulan · Pembentukan panitia · Berjalan · LPJ · Arsip**. Toolbar: search, filter periode, count ("4 proker"). Table columns: **Nama proker** (widest, with Repeat/Baru tag in muted text), **Ketupel**, **PJ**, **Hari-H**, **Status** (pill), **Anggaran terpakai** (right-aligned, % and Rupiah), kebab. Row click opens 4.4. Primary: **Usulkan proker**. A "Warisan periode lalu" section lists archived prokers each with three outlined actions **Ulangi · Arsipkan · Bubarkan**.

### 4.4 Detail proker (tabs inside)
Header: proker name 22 px, the **chain stepper**, status pill, Hari-H date with countdown, buttons **Ubah** (outlined). Tabs (2 px underline): **Ringkasan · Panitia · Tugas · Anggaran · Oprec · Dokumen**.
- **Ringkasan:** four short facts (Ketupel, PJ, Hari-H, Lokasi), a progress line "12 dari 20 tugas selesai", activity feed.
- **Panitia:** left column Ketupel + HOD + PJ as a small block; then one section per proker-division (Event, Inventory, People Relation, Marketing, PDD Dokum, PDD Design, Tech), each listing Koordinator and anggota with avatar, name, NIM (mono) and a "lapor ke" line. Button **Tambah divisi panitia**, **Tugaskan anggota** (opens modal 4.5). Show the headcount per division against target ("3 dari 5").
- **Tugas:** board with columns **Akan dikerjakan · Dikerjakan · Ditinjau · Selesai** (see 4.6).
- **Anggaran:** see 4.8.
- **Oprec:** the proker's vacancies and the applicant pipeline (4.9).
- **Dokumen:** proposal and LPJ files with status, upload button.

### 4.5 Modal "Tugaskan anggota"
Fields: Anggota (searchable by name/NIM), Divisi panitia, Peran (Koordinator / Anggota), Lapor ke (auto-filled). Under the form, a warning strip when the person already has 2+ active roles: "Andreas sudah memegang 2 peran aktif di proker lain." Buttons **Batal** (text) and **Tugaskan** (primary).

### 4.6 Tugas (board and detail)
Cards: title, divisi tag, due date (danger colour only when overdue), assignee avatars. Click opens a right side panel with description, assignees, comments, and the **chain stepper with the current approval step**. Buttons shown depend on the viewer's role: **Ajukan tinjauan** (Anggota), **Setujui / Kembalikan** (Koordinator, HOD/PJ, Ketupel). A returned task shows the reviewer's note.

### 4.7 Persetujuan (approvals inbox)
List of items awaiting the viewer: tugas, dokumen, pengeluaran anggaran, proker usulan. Each row: item title, proker, who submitted, the **chain stepper**, age ("2 hari"), and buttons **Setujui** (outlined) and **Kembalikan**. Tabs **Menunggu · Disetujui · Dikembalikan**. Explain the rule once in a muted line: "Persetujuan berjalan berurutan: Koordinator → HOD/PJ → Ketupel."

### 4.8 Anggaran (per proker and overall)
Top: KPI container (**Rencana · Realisasi · Sisa · Pemasukan**). Left: a table of categories by division with columns **Divisi, Rencana, Realisasi, Selisih** (selisih coloured good/danger text, numbers right-aligned). Right: entries list (date, description, amount, receipt link, submitted by). Buttons **Catat pengeluaran** (primary) and **Catat pemasukan** (outlined). A small tab switch **Rencana (proposal) / Realisasi (LPJ)**.

### 4.9 Oprec
- **Internal:** list of vacancies (title, proker, divisi, kuota, pelamar, tutup tanggal, status Draft/Dibuka/Ditutup) and a pipeline board per vacancy: **Melamar · Seleksi berkas · Wawancara · Diterima · Ditolak**, applicant cards with name, NIM, prodi, a note field. Button **Terima & masukkan ke panitia** on an accepted card.
- **Public page** (no sidebar, centred 720 px): proker name, division, quota, requirements, deadline, and an application form (Nama, NIM, Prodi, Angkatan, No. WhatsApp, Motivasi, upload CV) with one primary **Kirim lamaran**. Success state: "Lamaran terkirim. Kami hubungi lewat WhatsApp."

### 4.10 Pulse
Cohort list ("Pulse Angkatan 5": 8 peserta), table of interns with **Mentor, Kehadiran, Skor, Status** (Aktif/Lulus/Mundur), a pairing modal and a small evaluation form (skor 0-100, catatan). Tone: encouraging, plain.

### 4.11 Anggota
Directory: search, filter by divisi and status (Aktif, Pulse, Alumni). Table: **Nama (avatar), NIM (mono), Divisi, Jabatan, Proker aktif (count), Status**. Row opens a profile side panel with all current roles across prokers and workload.

### 4.12 Divisi & jobdesc
Six division sections, each with Koordinator, member count and a list of jobdesc (title, level). Buttons **Tambah jobdesc** (modal). The standing HOD roles (Ketua, Wakil, Sekretaris, Bendahara) shown as a separate block above.

### 4.13 Dokumen
Table of proposals and LPJ per proker with type, version, status (Draft, Diajukan, Disetujui), uploader, date, download/kebab. Upload modal.

### 4.14 Pengaturan
Two-column settings: **Organisasi** (name, type, fakultas), **Periode** (create/close a term, set active), **Peran & akses** (matrix of roles × permissions as switches), **Notifikasi**.

## 5. Sample data (use exactly; every total adds up)

**Organisation:** Himpunan Mahasiswa IMT, Universitas Ciputra · periode **2025/2026**.

**HOD:** Ketua **Clarissa Tan** · Wakil **Michelle Wijaya** · Sekretaris **Darren Lim** · Bendahara **Natalia Hendra**. Dosen Pembina: **Dr. Hendra Kusuma**. (All names are fictional.)

**Proker (4):**
| Proker | Jenis | Status | Ketupel | PJ | Hari-H |
|---|---|---|---|---|---|
| Techfest UC 2026 | Ulangi | Berjalan | Rafael Santoso | Michelle Wijaya | 12 Des 2026 |
| IMT Orientation Camp | Ulangi | Pembentukan panitia | Kevin Pratama | Clarissa Tan | 20 Des 2026 |
| National Hackathon UC | Baru | Usulan | Belum ditunjuk | Darren Lim | 18 Jan 2027 |
| IMT Class Meeting 2025 | (lama) | Arsip | Jessica Lau | Natalia Hendra | selesai |

**Techfest UC 2026 budget (Rupiah):**
| Divisi | Rencana | Realisasi |
|---|---|---|
| Event | 14.000.000 | 6.800.000 |
| Properties | 11.500.000 | 9.200.000 |
| Marketing | 6.000.000 | 1.500.000 |
| PDD Design | 4.000.000 | 3.600.000 |
| PDD Dokum | 5.000.000 | 2.100.000 |
| Tech | 3.500.000 | 2.400.000 |
| People Relation | 4.500.000 | 1.250.000 |
| **Total** | **48.500.000** | **26.850.000** (55 % terpakai) |

Pemasukan: sponsor **20.000.000** + tiket **12.600.000** (84 tiket × 150.000) = **32.600.000**. Sisa kas = 32.600.000 − 26.850.000 = **5.750.000**. Properties is at 80 % of its plan: flag it with warning text.

**Panitia Techfest:** Koordinator Tech **Andreas Gunawan**, Event **Felicia Hartono**, PDD Design **Clarissa Putri**, Properties **Bryan Santoso**; 3-5 anggota per divisi with 13-digit NIM (mono). Show one person (Andreas) with roles in two prokers.

**Tugas sample:** "Deploy landing page registrasi peserta" (Tech, selesai) · "Sistem QR check-in & e-certificate" (Tech, dikerjakan, tenggat 5 Des) · "Master rundown & brief pembicara" (Event, ditinjau at step Koordinator) · "Booking Auditorium UC & izin alat" (Properties, **lewat tenggat**) · "Key visual & desain lanyard" (PDD Design, selesai). 20 tugas total, 12 selesai.

**Oprec Techfest, divisi Tech, kuota 3:** 11 pelamar → Melamar 4 · Seleksi berkas 3 · Wawancara 2 · Diterima 1 · Ditolak 1.

**Pulse Angkatan 5:** 8 peserta → Aktif 6 · Lulus 1 · Mundur 1.

**Ringkasan KPI:** Proker aktif 3 · Anggota aktif 42 · Persetujuan menunggu 5 · Tugas lewat tenggat 4.

## 6. Role switcher (important for the prototype)

In the top bar, a small select **"Tampilkan sebagai"** lets the reviewer switch role: **Anggota · Koordinator · HOD/PJ · Ketupel · Dosen Pembina**. It changes (a) which sidebar items appear, (b) which buttons show on tasks and approvals, (c) what the overview emphasises. Example: Anggota sees only **Panitia saya** and their tasks; Ketupel sees one proker's full workspace; HOD/PJ sees all prokers; Dosen Pembina is read-only with a **Beri catatan** action.

## 7. Phone (390 px)

The tinted frame disappears: the sheet becomes the screen with a 48 px top bar (menu button, title, one action). The sidebar opens as a full-height sheet. KPI strip becomes a 2×2 grid; the right rail drops below the main column; tables become two-line rows (title + key number on line 1, secondary fields in grey on line 2, kebab at right); tabs scroll horizontally; the task board becomes a column list with a column switcher; touch targets at least 44 px.

## 8. Interactions to make clickable

Sidebar and tab navigation · row → detail · **Tugaskan anggota** modal with the workload warning · approving a task advances the chain stepper one step · **Terima & masukkan ke panitia** moves an applicant card and adds a row to Panitia · role switcher · create task, create jobdesc, record expense (modals) · public Oprec form submit success state.

## 9. Handoff requirements

- Use clean auto-layout, reusable components named in English (`Sidebar`, `TopBar`, `KpiStrip`, `DataTable`, `StatusPill`, `ChainStepper`, `TaskCard`, `Modal`, `SidePanel`) and the colour/type tokens above as variables. Do not hard-code one-off colours.
- The result will be re-built as **Laravel Blade + Tailwind CSS + Alpine.js**, so keep layouts to simple flex/grid structures and avoid effects Tailwind cannot reproduce.
- Language of all UI text: **Indonesian**. Dates like "12 Des 2026", money like "Rp 48.500.000", 24-hour times.
