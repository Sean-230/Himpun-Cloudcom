# Himpun — Claude Code Guidelines

Read `docs/pembagian-tugas.md` and `README.md` before making any changes.

## 1. Respect Team Ownership (BB, Sean, Dzacky)
- Confirm which developer you are working for (**BB**, **Sean**, or **Dzacky**) and **only edit files owned by that developer** (see `docs/pembagian-tugas.md` → *File ownership map* and *Route jobdesc*).
- Never edit an existing migration that has already been committed; always add a new migration in your owner's timestamp range (`BB: 2026_12_01_1*`, `Sean: 2026_12_01_2*`, `Dzacky: 2026_12_01_3*`).
- Do not modify `composer.json`, `package.json`, or lockfiles unless working for BB. Do not install `laravel/boost`.
- `app/Support/DummyData.php` is frozen (Sean removes it in the closing PR).

## 2. Mandatory Auto-Update of `README.md` & `docs/pembagian-tugas.md`
Whenever you finish implementing a task or feature for your developer — **without waiting to be asked**:
1. Run verification first:
   - `php artisan test` (or your feature's PHPUnit tests)
   - `node --test "tests/js/*.test.mjs"`
   - `vendor/bin/pint --test <your changed PHP files>`
2. Only after tests and Pint pass, update **both** `README.md` and `docs/pembagian-tugas.md`:
   - Check off `[ ]` → `[x]` **only under your owner's section** and adjust their remaining points.
   - Update **only your owner's row(s)** in `README.md` → `## 📊 Feature Status & Roadmap`.
   - If you added a migration, table, or model, update **only your owner's row(s)** in `README.md` → `## 🗄️ Database Structure`.
3. **Never edit another teammate's section** in `README.md` or `docs/pembagian-tugas.md` to avoid merge conflicts.

## 3. Database & Stack Notes
- Production database is **Supabase PostgreSQL** (schema `himpun`, Session Pooler port `5432`, `DB_SSLMODE=require`). Local tests use SQLite `:memory:` via `phpunit.xml`.
- Keep queries compatible with **both SQLite and PostgreSQL** (e.g., use `LOWER(col) LIKE ?` instead of case-sensitive `LIKE`, add explicit `$table->index(...)` on new FK columns — never chain `->constrained()->index()`).
- Timezone is `Asia/Jakarta` (WIB).
