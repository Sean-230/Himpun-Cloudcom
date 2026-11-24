# Work split: Sean, BB and Dzacky

Himpun is a clickable prototype that reads everything from `DummyData`. The job now is to make it real: login, a database, and the server-side rules. The work is cut into three parts of **equal difficulty (about 25 points each)** so nobody waits on anyone and **every file has exactly one owner**.

Points are relative effort (1 = small, 5 = a solid chunk of work), not hours. They were estimated from the audit, not measured.

| | Part 1: **BB** | Part 2: **Sean** | Part 3: **Dzacky** |
|---|---|---|---|
| Theme | Access, organisation data, admin | Writing prokers, delivery | Requests, reading prokers, dashboards |
| Points | **25** | **25** | **24** |
| Why this person | Built the admin console | Owns the proker form, CI and deploy | Built the division-request feature |

If someone falls behind, these can move to another person with no file conflicts: BB's 1.9 (new pages), Sean's 2.8 (docs and cleanup), and Dzacky's 3.7 (dashboards).

## Golden rules (all three)

1. **Only edit files you own** (ownership map below). Need a change in someone else's file: open a tiny PR to `main`, merge it fast, everyone rebases.
2. **Never edit an old migration.** Anything that already ran on the server will not run again. Always add a new file.
3. **Do not reformat, rename, or move files you do not own.** No repo-wide `pint`, `composer update`, or `npm update`. Use `vendor/bin/pint <your files>`.
4. **Rebase daily:** `git fetch origin && git rebase origin/main`. Small PRs (under about 400 lines), one topic per PR.
5. **`DummyData.php` is frozen.** Nobody edits or deletes it. Build new code next to it and move your own pages over. Sean deletes it in the closing PR.
6. **Lockfiles and `package.json` / `composer.json` are BB's.** Need a package: ask BB.

## Step 0: preparation (do once, before anyone starts)

One PR to `main`, then all three branch from it. This is what makes the rest conflict-free.

| # | What | Why |
|---|---|---|
| 0.1 | Split `routes/web.php` into a thin loader plus `routes/auth.php` (BB), `routes/proker_write.php` (Sean), `routes/proker_read.php` (Dzacky), `routes/request.php` (Dzacky) | One shared route file is the biggest conflict source |
| 0.2 | Move Alpine components to `resources/js/components/` and make `app.js` register them automatically (`import.meta.glob`) | A new component no longer edits `app.js` |
| 0.3 | Move the proker functions out of `Access.php` into `App\Support\ProkerAccess` (`canSee`, `listFor`, `roleLabel`, `divisionsCoordinated`). `Access.php` keeps identity only | Two owners, two files |
| 0.4 | Split `RoleAccessTest` and `ExampleTest` per part, and add `tests/Concerns/ActsAsRole.php` (`$this->as($role, $name)`) | Tests stop overwriting each other, and when BB changes how identity works only this helper changes |
| 0.5 | `DatabaseSeeder` calls three empty seeders: `OrganisationSeeder` (BB), `ProkerSeeder` (Sean), `RequestSeeder` (Dzacky) | Nobody edits `DatabaseSeeder` again |
| 0.6 | Run Pint once over the whole repo and add `.github/CODEOWNERS` from the ownership map | No format noise later, and GitHub asks the owner to review |

## Part 1: BB, access, organisation data, admin (25 points)

Goal: close the biggest audit finding (anyone is automatically HOD) and own the people and organisation data.

| # | Task | Pts | Done when |
|---|---|:-:|---|
| 1.1 | **Organisation and people data.** Models `Organisation`, `Term`, `Division`, `Membership`, and `OrganisationSeeder` turning the 42-person roster in `DummyData::ROSTER` into `users` + `memberships` | 3 | `php artisan db:seed` creates 42 users, 6 divisions, 1 term. **Merge this PR first** |
| 1.2 | **Real login and logout** with Laravel's built-in session auth (no new package): login page, `routes/auth.php`, `resources/views/auth/*` | 3 | Guests are sent to login |
| 1.3 | **Role from the account.** `Access::me()` reads `Auth::user()`; guests get the lowest access, never HOD. Global role (HOD) from the user, per-proker role from `proker_assignments` | 4 | No code path falls back to HOD |
| 1.4 | **Restrict `/peran` and `/admin`.** The role switcher only works when `APP_ENV=local`; `/admin` gets `role:hod` | 1 | In production `/peran` is a 404 and `/admin` asks for login |
| 1.5 | **Layout header and menu:** account menu (sign in/out), switcher only locally, menu links for the new pages | 1 | |
| 1.6 | **Security hardening:** `SESSION_SECURE_COOKIE=true`, HSTS and a basic CSP header, and written steps for the SSL certificate on CloudPanel | 2 | `curl -I` shows the headers, certificate verifies |
| 1.7 | **Access tests** and the `ActsAsRole` helper (create a real user per role) | 2 | Guest, role and switcher tests pass |
| 1.8 | **Bring the admin console into the main layout** (it has its own colours and layout) | 3 | `/admin` uses `layouts.app` and the shared components |
| 1.9 | **Two new pages:** Members directory and Divisions & jobdesc (master data; the menu items are placeholders today) | 6 | List, search, and create/edit divisions and jobdesc |
| | **Total** | **25** | |

BB must keep stable: `Access::me()` still returns `['role','name','label']` (adding `'id'` is fine), `Access::is()` and `Access::home()` keep their signatures, and the middleware `role:` keeps working.

## Part 2: Sean, writing prokers and delivery (25 points)

Goal: make creating a proker real, and make shipping reliable.

| # | Task | Pts | Done when |
|---|---|:-:|---|
| 2.1 | **Models and migrations.** `Proker`, `ProkerDivision`, `ProkerAssignment` for the existing tables, plus two new tables (contracts below) and a `ProkerStatus` enum with the labels the UI uses | 5 | `migrate` is clean on MySQL and the CI migrate passes. **Merge this PR second** (Dzacky needs it) |
| 2.2 | **Save a proker on the server.** `POST /proker` with a Form Request: one person one role, Ketupel is not PJ, valid jobdesc, Hari-H not in the past. These rules live only in JavaScript today | 5 | Every violation returns 422 in a test |
| 2.3 | **Create form** shows server errors and redirects to the saved proker | 2 | |
| 2.4 | **`ProkerSeeder`** from the dummy prokers | 2 | Seeded prokers look like the dummy ones |
| 2.5 | **Tests:** create-flow PHP tests and the `proker-form` JS tests | 3 | Both suites green |
| 2.6 | **Timezone** `Asia/Jakarta` in `config/app.php`, with tests using `Carbon::setTestNow` | 1 | |
| 2.7 | **CI/CD overhaul** in `.github/workflows`: `npm ci`, run the JS tests and `pint --test`, `permissions: contents: read`, SSH key login, a deploy that never leaves the site without `vendor`, and tests that really use SQLite | 4 | A push to `main` does not break the site during deploy |
| 2.8 | **README, `docs/DEPLOY.md`, delete dead files** (`welcome.blade.php`) | 2 | README matches reality |
| 2.9 | **Closing PR:** delete `DummyData.php` and its test, run Pint over the repo | 1 | |
| | **Total** | **25** | |

## Part 3: Dzacky, requests, reading prokers, dashboards (24 points)

Goal: finish the division-request feature and move every read-only screen onto the database.

| # | Task | Pts | Done when |
|---|---|:-:|---|
| 3.1 | **Requests in the database.** Table `division_requests` (contract below), model, controller, policy | 4 | A request from one coordinator shows up for the other |
| 3.2 | **Request rules:** only the receiver answers, valid status order, divisions validated, ids from the database (not `now()->timestamp`) | 2 | A test per rule |
| 3.3 | **Request page fixes:** replace `old()` inside JS strings with `@js(...)`, keyboard-friendly tabs, menu badge counted from the database | 2 | No `'{{ old(...) }}'` inside `x-data` |
| 3.4 | **Request tests and `RequestSeeder`** | 2 | |
| 3.5 | **`ProkerAccess` from the database and the proker list** (`/proker`) | 3 | List shows only what each role may see |
| 3.6 | **Proker detail saves changes:** tick jobdesc, add/remove jobdesc and meetings (HOD only) with a "Saved" message | 5 | Reload keeps the change, PJ and coordinators get 403 |
| 3.7 | **Dashboards (HOD and PJ) from the database,** fix the "-N hari" label for a Hari-H that has passed, and remove the copied month and status-colour maps in those views | 4 | No dashboard calls `DummyData` |
| 3.8 | **Tests** for the list, detail and dashboards (PHP and the `proker-detail` JS tests) | 2 | Both suites green |
| | **Total** | **24** | |

Dzacky starts with 3.1 to 3.4 (they need nothing from the others, use factories) and begins 3.5 once Sean's 2.1 PR is merged.

## Contracts between parts

**Sean provides (PR 2.1).** Table and column names are fixed here so Dzacky can code against them:

| Table / model | Columns |
|---|---|
| `prokers`, `proker_divisions`, `proker_assignments` (existing) | Models `Proker`, `ProkerDivision`, `ProkerAssignment` with `Proker::divisions()`, `Proker::assignments()`, `ProkerDivision::jobdescs()` |
| `proker_division_jobdescs` (new, `ProkerDivisionJobdesc`) | `proker_division_id` (FK, cascade), `title` (120), `days_before` (int, negative = after Hari-H), `done` (bool, default false) |
| `proker_meetings` (new, `ProkerMeeting`) | `proker_id` (FK, cascade), `scope` (`pleno`, `koor` or `division`), `proker_division_id` (nullable FK, set when scope is `division`), `weekday` (0 to 6, 0 = Sunday), `starts_at` (time), `place` (nullable) |
| `ProkerStatus` enum | `Usulan`, `Pembentukan panitia`, `Berjalan`, `LPJ`, `Selesai`. Today the migration comment lists different words than the UI uses; the enum settles it |

**Dzacky provides.** `division_requests`: `proker_id` (FK), `from_proker_division_id` and `to_proker_division_id` (FKs), `from_user_id` and `to_user_id` (FKs to `users`), `title` (150), `note` (nullable), `due_on` (date), `status` (`Menunggu`, `Diproses`, `Selesai`, `Ditolak`).

**BB provides.** Seeded users, memberships and standing divisions (1.1), `Access::me()` (1.3), and the `ActsAsRole` test helper (1.7). Until 1.3 lands, `ActsAsRole` simply sets the demo session, so Sean's and Dzacky's tests keep passing.

## File ownership map

| Path | Owner |
|---|---|
| `app/Support/Access.php`, `app/Http/Middleware/**`, `app/Models/User.php`, `AppServiceProvider.php`, `bootstrap/**`, `config/auth.php`, `config/session.php` | **BB** |
| `app/Models/{Organisation,Term,Division,Membership}.php`, `OrganisationSeeder` | **BB** |
| `routes/auth.php` (includes `/` and `/admin`) | **BB** |
| `resources/views/layouts/**`, `admin/**`, `errors/**`, `auth/**`, new members and divisions pages, `resources/css/app.css`, `resources/js/components/role-switch.js` | **BB** |
| `composer.json`, `composer.lock`, `package.json`, `package-lock.json`, `vite.config.js`, `.env.example` | **BB** |
| `app/Models/Proker*.php`, `app/Enums/ProkerStatus.php`, `ProkerSeeder`, proker store controller and Form Request | **Sean** |
| `routes/proker_write.php`, `resources/views/proker/create.blade.php`, `resources/js/components/proker-form.js` | **Sean** |
| `config/app.php` (timezone only), `.github/**`, `README.md`, `docs/**`, `phpunit.xml`, `welcome.blade.php` | **Sean** |
| `app/Support/ProkerAccess.php`, `app/Models/DivisionRequest.php`, request and proker-read controllers and Form Requests, `RequestSeeder` | **Dzacky** |
| `routes/proker_read.php`, `routes/request.php` | **Dzacky** |
| `resources/views/proker/index.blade.php`, `proker/show.blade.php`, `dashboard*.blade.php`, `request/**`, `resources/js/components/proker-detail.js` | **Dzacky** |
| `app/Support/DummyData.php`, `DatabaseSeeder.php`, `resources/js/app.js`, `resources/js/dates.js`, `resources/views/components/*`, `routes/web.php`, `Controller.php` | **frozen** |
| `database/migrations/` | new files only. Timestamp ranges: BB `2026_12_01_1*`, Sean `2026_12_01_2*`, Dzacky `2026_12_01_3*` |
| `tests/` | per feature: access and auth BB, proker create and its JS tests Sean, proker read, dashboards, requests and their JS tests Dzacky |

## Order

1. **Step 0** merged to `main`. All three branch from it.
2. **BB 1.1** (organisation and user seed) merged first, within about two days.
3. **Sean 2.1** (models and migrations) merged second, within about two days. Dzacky works on 3.1 to 3.4 meanwhile.
4. Everyone works in parallel. They never touch the same files.
5. **BB 1.2, 1.3 and 1.7 (real login) are merged last** among the feature PRs, because they change how identity is read in every test. Since tests go through `ActsAsRole`, only that helper adjusts.
6. **Sean 2.9** closing PR: delete `DummyData`, run Pint on the whole repo.

## Definition of done (every PR)

- `php artisan test` green and `node --test "tests/js/*.test.mjs"` green.
- `vendor/bin/pint --test <your files>` clean.
- No file outside your ownership changed: check `git diff --stat origin/main`.
