# Work split: BB, Sean and Dzacky (Rebalanced — 2026-11-27)

Himpun is moving from a clickable `DummyData` prototype to a real Laravel 13 + **Supabase (PostgreSQL)** app with real login, database persistence, and automated CI/CD.

Because **BB** already merged the foundation of Part 1 on `main` (`7ed1137`: organisation/people models, `OrganisationSeeder`, `/login` & `/logout`, `/anggota`, `/divisi`, and `/admin` layout), the **remaining work** has been rebalanced to **18 points each** so all three finish together with **zero overlapping files**.

Points are relative effort (1 = small, 5 = a solid chunk of work), not hours.

| | Part 1: **BB** | Part 2: **Sean** | Part 3: **Dzacky** |
|---|---|---|---|
| Theme | Auth polish, security, `/admin` DB, dashboards | Proker models & write path, Supabase, CI/CD & cleanup | Division requests, proker list & detail persistence |
| Already merged / drafted | ~17 pts merged on `main` (`7ed1137`) | Prototype form + CI baseline | ~3 pts drafted on `origin/dzaky` (`e847c4d`) |
| **Remaining points** | **18** | **18** | **18** |
| Why this person | Owns identity, security, layout, `/admin` & dashboards | Owns proker schema/create, Postgres/Supabase & delivery | Owns division requests, `ProkerAccess` & proker detail |

## Golden rules (all three)

1. **Only edit files you own** (ownership map below). Need a change in someone else's file: open a tiny PR to `main`, merge it fast, everyone rebases.
2. **Never edit an old migration.** Anything that already ran on the server will not run again. Always add a new file.
3. **Do not reformat, rename, or move files you do not own.** No repo-wide `pint`, `composer update`, or `npm update`. Use `vendor/bin/pint <your files>`.
4. **Rebase daily:** `git fetch origin && git rebase origin/main`. Small PRs (under about 400 lines), one topic per PR.
5. **`DummyData.php` is frozen.** Nobody edits or deletes it. Build new code next to it and move your own pages over. Sean deletes it in the closing PR (2.6).
6. **Lockfiles and `package.json` / `composer.json` are BB's.** Need a package: ask BB.

## Step 0: preparation (do once on `main` before new PRs)

One small PR to `main`, then all three rebase on it. This fixes the current merge/test conflict bottleneck.

| # | What | Why |
|---|---|---|
| 0.1 | Split `routes/web.php` into a thin loader plus `routes/auth.php` (BB), `routes/proker_write.php` (Sean), `routes/proker_read.php` (Dzacky), `routes/request.php` (Dzacky) | One 266-line shared route file blocks `route:cache` and causes conflicts on every PR |
| 0.2 | Move Alpine components to `resources/js/components/` and make `app.js` register them automatically (`import.meta.glob`) | A new component no longer edits `app.js` |
| 0.3 | Move proker visibility helpers out of `Access.php` into `App\Support\ProkerAccess` (`canSee`, `listFor`, `roleLabel`, `divisionsCoordinated`). `Access.php` keeps identity only | Two owners, two files |
| 0.4 | Add `tests/Concerns/ActsAsRole.php` (`$this->as($role, $name)`) and split feature tests per part | Unbreaks the 17 failing PHPUnit tests and stops test-file collisions |
| 0.5 | `DatabaseSeeder` calls three seeders: `OrganisationSeeder` (BB), `ProkerSeeder` (Sean), `RequestSeeder` (Dzacky) | Nobody edits `DatabaseSeeder` again |
| 0.6 | Run Pint over the 10 failing files on `main` and add `.github/CODEOWNERS` from the ownership map | Gets CI style check green and auto-assigns PR reviewers |

---

## Part 1: BB — Access, Organisation, `/admin` & Dashboards (18 remaining pts)

**Already merged on `main` (`7ed1137`):**
- [x] Models `Organisation`, `Term`, `Division`, `Membership`, `Jobdesc` + `OrganisationSeeder` (42 members, 6 divisions, 1 term)
- [x] Login & Logout (`/login`, `/logout`)
- [x] Members Directory (`/anggota`) and Divisions & Jobdesc CRUD (`/divisi`, `/jobdesc`)
- [x] Admin Console moved into `layouts.app` with `role:hod`

**Remaining features:**
- [ ] **1.1 Auth & Per-Proker Roles (3 pts):** Protect app routes with `auth` so guests redirect to `/login`; resolve per-proker roles (`pj`, `koor`, `ketupel`) from `proker_assignments` (after Sean's 2.1); unify `dosen`/`isHod()`; memoize `Access::me()` per request.
- [ ] **1.2 Account Menu & Local-Only Switcher (2 pts):** Return 404 on `/peran` outside `local`; hide demo switcher & skip `$roleOptions` outside `local`; add Sign In / Sign Out in `layouts/app.blade.php`; wire pending request badge via `DivisionRequest::pendingCountFor($me)`.
- [ ] **1.3 Security & Postgres Fixes (3 pts):** Add `throttle:5,1` on `POST /login`; set `SESSION_SECURE_COOKIE=true`, HSTS & basic CSP headers; make `/anggota` search case-insensitive on Supabase Postgres (`LOWER(...)`); scope `/divisi` & `/jobdesc` PATCH to user's organisation; delete dead `adminView.blade.php`.
- [ ] **1.4 Auth & Master-Data Tests (3 pts):** Add `ActsAsRole` helper, fix the 17 failing PHPUnit tests on `main`, fix `AccessTest:L82`, delete `tests/Unit/ExampleTest.php`, and add feature tests for `/login`, `/logout`, `/anggota`, `/divisi`, `/jobdesc`, `/admin`.
- [ ] **1.5 Live Admin Console (3 pts):** Replace hardcoded Alpine arrays in `adminDasboard.blade.php` (Ketupel/PJ assignment, division jobdesc monitor, timeline) with real Eloquent queries and save endpoints (after Sean's 2.1).
- [ ] **1.6 Live HOD & PJ Dashboards (4 pts · *rebalanced from Dzacky*):** Move `dashboard.blade.php` & `dashboard-pj.blade.php` off `DummyData`; fix division-by-zero & null crashes on empty DB; fix `"-N hari"` / `"hari ini"` bug on past Hari-H; use `ProkerStatus` enum instead of copied maps; add dashboard tests.

BB must keep stable: `Access::me()` returns `['role', 'name', 'label']` (adding `'id'` is fine), `Access::is()` and `Access::home()` keep their signatures, and the `role:` middleware keeps working.

---

## Part 2: Sean — Proker Creation, Supabase & Delivery (18 remaining pts)

**Remaining features:**
- [x] **2.1 Proker Models, Migrations & `ProkerStatus` Enum (5 pts · *built on `Sean` branch*):** Models `Proker`, `ProkerDivision`, `ProkerAssignment`, `ProkerDivisionJobdesc`, `ProkerMeeting`; new migrations `2026_12_01_2*` for `proker_division_jobdescs`, `proker_meetings`, nullable `prokers.location`, `UNIQUE(proker_id, membership_id)` on `proker_assignments`, `UNIQUE(proker_id, name)` on `proker_divisions` & Postgres FK indexes; `App\Enums\ProkerStatus` with labels & pill colours.
- [ ] **2.2 Create Proker Backend (4 pts):** `POST /proker` controller + `StoreProkerRequest` inside a DB transaction enforcing: 1 person 1 role per proker, Ketupel ≠ PJ, valid jobdesc titles & integer targets, Hari-H ≥ today, `repeat` requires `previous_proker_id` (422 on every violation).
- [ ] **2.3 Create Proker Form & `ProkerSeeder` (3 pts):** Feed real DB members & past prokers into `create.blade.php`, show server 422 errors, redirect to saved proker; write `ProkerSeeder` seeding the 7 baseline prokers (with divisions, assignments, jobdescs, meetings).
- [ ] **2.4 Create-Flow Tests & `Asia/Jakarta` Timezone (2 pts):** Feature tests for `POST /proker` (happy path + every 422 rule), keep `tests/js/proker-form.test.mjs` green, set `'timezone' => 'Asia/Jakarta'` in `config/app.php` with `Carbon::setTestNow` tests.
- [ ] **2.5 Supabase PostgreSQL & CI/CD Overhaul (3 pts):** Configure Postgres (`config/database.php` `DB_SCHEMA` / private schema or RLS lock-down, Session Pooler port 5432, `DB_SSLMODE=require`); overhaul `.github/workflows/deploy.yaml` (`postgres:16` service + `pgsql` ext, `npm ci`, JS tests + `pint --test`, `permissions: contents: read`, SSH key auth, zero-downtime `vendor`/`build` swap).
- [ ] **2.6 Docs & Final Cleanup PR (1 pt):** Keep `README.md` accurate, write `docs/DEPLOY.md` (CloudPanel + Supabase setup & SSL), delete `welcome.blade.php`, and (last, after BB & Dzacky finish) delete `DummyData.php` + `DummyDataTest.php` and run Pint across the repo.

---

## Part 3: Dzacky — Division Requests, Proker List & Proker Detail (18 remaining pts)

**Drafted on `origin/dzaky` (`e847c4d`):**
- [x] `division_requests` migration (`2026_12_01_300000`), `DivisionRequest` model, `DivisionRequestPolicy`, `StoreDivisionRequest`, `UpdateDivisionRequestStatusRequest`

**Remaining features:**
- [ ] **3.1 Division Requests Backend (3 pts):** Wire `DivisionRequestController` + `routes/request.php` to DB; enforce sender coordinates `from_division`, receiver-only answers, valid status transitions (`Menunggu → Diproses/Ditolak`, `Diproses → Selesai`), validated divisions, DB IDs; expose `DivisionRequest::pendingCountFor(array $me): int`.
- [ ] **3.2 Division Requests UI & XSS Fixes (2 pts):** Replace `'{{ old(...) }}'` and raw Blade interpolation inside Alpine attributes (`request/index.blade.php:L32-34,L84`) with `@js(...)`; remove unused `$aktifProkers`; keyboard-friendly tabs.
- [ ] **3.3 Division Requests Tests & `RequestSeeder` (2 pts):** Feature tests covering every rule (sender/receiver/status/403s) + `RequestSeeder`.
- [ ] **3.4 Live Proker List & `ProkerAccess` (3 pts):** Move `canSee`, `listFor`, `roleLabel`, `divisionsCoordinated` onto Eloquent in `App\Support\ProkerAccess`; move `proker/index.blade.php` off `DummyData` using `ProkerStatus` enum (after Sean's 2.1).
- [ ] **3.5 Live Proker Detail (5 pts):** Move `proker/show.blade.php` off `DummyData`; add endpoints to tick/add/delete `ProkerDivisionJobdesc` and add/delete `ProkerMeeting` (HOD only, 403 for PJ/Koordinator/Anggota) with a "Saved" indicator.
- [ ] **3.6 Proker List & Detail Tests (3 pts):** Feature tests for `/proker`, `/proker/{id}`, jobdesc/meeting CRUD & 403 guards; keep `tests/js/proker-detail.test.mjs` green.

Dzacky finishes 3.1–3.3 first (independent), then starts 3.4–3.6 right after Sean's 2.1 merges.

---

## Contracts between parts

**Sean provides (PR 2.1).** Table, column, and enum names are fixed here so BB and Dzacky can code against them:

| Table / model | Columns / API |
|---|---|
| `prokers` (existing + `location`) | Model `Proker` with `divisions()`, `assignments()`, `meetings()`, `previousProker()`, `organisation()`, `term()`, cast `status` → `ProkerStatus` |
| `proker_divisions` (existing) | Model `ProkerDivision` with `proker()`, `division()`, `assignments()`, `jobdescs()` |
| `proker_assignments` (existing + `UNIQUE(proker_id, membership_id)`) | Model `ProkerAssignment` with `proker()`, `membership()`, `prokerDivision()`, `reportsTo()` |
| `proker_division_jobdescs` (new, `ProkerDivisionJobdesc`) | `proker_division_id` (FK cascade), `title` (120), `days_before` (int, negative = after Hari-H), `done` (bool default `false`) |
| `proker_meetings` (new, `ProkerMeeting`) | `proker_id` (FK cascade), `scope` (`pleno`, `koor`, `division`), `proker_division_id` (nullable FK), `weekday` (`0`–`6`, `0` = Sunday), `starts_at` (time), `place` (nullable) |
| `App\Enums\ProkerStatus` | Backed string enum: `Usulan`, `Pembentukan panitia`, `Berjalan`, `LPJ`, `Selesai`, with `label(): string` and `pillClass(): string` |

**Dzacky provides.** `division_requests`: `proker_id` (FK), `from_proker_division_id` & `to_proker_division_id` (FKs), `from_user_id` & `to_user_id` (FKs to `users`), `title` (150), `note` (nullable), `due_on` (date), `status` (`Menunggu`, `Diproses`, `Selesai`, `Ditolak`), plus `DivisionRequest::pendingCountFor(array $me): int` for the sidebar badge.

**BB provides.** Seeded users, memberships and standing divisions (`OrganisationSeeder`), `Access::me()` (1.1), and the `ActsAsRole` test helper (1.4).

---

## File ownership map

| Path | Owner |
|---|---|
| `app/Support/Access.php`, `app/Http/Middleware/**`, `app/Models/{User,Organisation,Term,Division,Membership,Jobdesc}.php`, `app/Providers/AppServiceProvider.php`, `bootstrap/**`, `config/{auth,session}.php` | **BB** |
| `database/seeders/OrganisationSeeder.php`, auth/members/divisions/admin/dashboard controllers & Form Requests | **BB** |
| `routes/auth.php` (`/`, `/login`, `/logout`, `/peran`, `/dashboard`, `/admin`, `/anggota`, `/divisi`, `/jobdesc`) | **BB** |
| `resources/views/{layouts,admin,auth,errors,members,divisions}/**`, `resources/views/dashboard*.blade.php`, `resources/css/app.css`, `resources/js/components/role-switch.js` | **BB** |
| `composer.json`, `composer.lock`, `package.json`, `package-lock.json`, `vite.config.js`, `.env.example` | **BB** |
| `app/Models/Proker*.php`, `app/Enums/ProkerStatus.php`, `database/seeders/ProkerSeeder.php`, proker-store controller & `StoreProkerRequest` | **Sean** |
| `routes/proker_write.php`, `resources/views/proker/create.blade.php`, `resources/js/components/proker-form.js` | **Sean** |
| `config/app.php` (timezone), `config/database.php` (Supabase `pgsql` schema/pooler), `.github/**`, `README.md`, `docs/**`, `phpunit.xml`, `resources/views/welcome.blade.php` | **Sean** |
| `app/Support/ProkerAccess.php`, `app/Models/DivisionRequest.php`, `app/Policies/DivisionRequestPolicy.php`, request & proker-read/detail controllers & Form Requests, `database/seeders/RequestSeeder.php` | **Dzacky** |
| `routes/proker_read.php`, `routes/request.php` | **Dzacky** |
| `resources/views/proker/index.blade.php`, `resources/views/proker/show.blade.php`, `resources/views/request/**`, `resources/js/components/proker-detail.js` | **Dzacky** |
| `app/Support/DummyData.php`, `DatabaseSeeder.php`, `resources/js/app.js`, `resources/js/dates.js`, `resources/views/components/*`, `routes/web.php`, `Controller.php` | **frozen** *(Sean deletes `DummyData` in 2.6)* |
| `database/migrations/` | new files only. Timestamp ranges: BB `2026_12_01_1*`, Sean `2026_12_01_2*`, Dzacky `2026_12_01_3*` |
| `tests/` | BB: `ActsAsRole`, access/auth/members/divisions/admin/dashboards · Sean: proker-create + `proker-form.test.mjs` · Dzacky: requests, proker list/detail + `proker-detail.test.mjs` |

---

## Merge order

1. **Step 0** merged to `main` (routes split, `DatabaseSeeder`, Pint fix, `ActsAsRole` scaffold). All three rebase on `main`.
2. **BB 1.4** (unbreak the 17 failing PHPUnit tests on `main`) + **Sean 2.1** (Proker models, migrations, `ProkerStatus`) merge first. Dzacky finishes 3.1–3.3 in parallel.
3. Everyone works in parallel with zero file collisions:
   - BB: 1.1, 1.2, 1.3, 1.5, 1.6
   - Sean: 2.2, 2.3, 2.4, 2.5
   - Dzacky: 3.4, 3.5, 3.6
4. **Sean 2.6** closing PR: delete `DummyData.php` + `DummyDataTest.php`, run Pint across the repo, verify `DEPLOY.md` & `README.md`.

## Definition of done (every PR)

- `php artisan test` green and `node --test "tests/js/*.test.mjs"` green.
- `vendor/bin/pint --test <your files>` clean.
- No file outside your ownership changed: check `git diff --stat origin/main`.
