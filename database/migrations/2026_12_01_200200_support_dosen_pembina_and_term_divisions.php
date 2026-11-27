<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Pre-registered students (password nullable until /register) + Smart Delete (softDeletes).
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable()->change();
            $table->timestamp('activated_at')->nullable()->after('password');
            $table->softDeletes();
        });

        // Mark already-seeded users with passwords as activated.
        DB::table('users')
            ->whereNotNull('password')
            ->whereNull('activated_at')
            ->update(['activated_at' => now()]);

        // 2. Persistent Dosen Pembina link per organisation (works across all terms 25/26, 26/27, 27/28).
        Schema::table('organisations', function (Blueprint $table) {
            $table->foreignId('advisor_user_id')->nullable()->after('faculty')->constrained('users')->nullOnDelete();
            $table->index('advisor_user_id', 'organisations_advisor_user_id_idx');
        });

        // 3. Make divisions term-scoped so each period (25/26, 26/27, ...) can freely rename/add/remove divisions.
        Schema::table('divisions', function (Blueprint $table) {
            $table->foreignId('term_id')->nullable()->after('organisation_id')->constrained('terms')->cascadeOnDelete();
            $table->index('term_id', 'divisions_term_id_idx');
        });

        // 4. Make jobdescs term-scoped so HOD roles (default 6 slots) and division roles can vary per period.
        Schema::table('jobdescs', function (Blueprint $table) {
            $table->foreignId('term_id')->nullable()->after('organisation_id')->constrained('terms')->cascadeOnDelete();
            $table->index('term_id', 'jobdescs_term_id_idx');
        });

        // Backfill existing divisions & jobdescs to each organisation's active (or first) term.
        $orgIds = DB::table('organisations')->pluck('id');
        foreach ($orgIds as $orgId) {
            $termId = DB::table('terms')
                ->where('organisation_id', $orgId)
                ->orderByDesc('is_active')
                ->orderBy('id')
                ->value('id');

            if ($termId) {
                DB::table('divisions')
                    ->where('organisation_id', $orgId)
                    ->whereNull('term_id')
                    ->update(['term_id' => $termId]);

                DB::table('jobdescs')
                    ->where('organisation_id', $orgId)
                    ->whereNull('term_id')
                    ->update(['term_id' => $termId]);
            }
        }

        // Replace UNIQUE(organisation_id, name) with UNIQUE(organisation_id, term_id, name).
        Schema::table('divisions', function (Blueprint $table) {
            $table->dropUnique(['organisation_id', 'name']);
            $table->unique(['organisation_id', 'term_id', 'name'], 'divisions_org_term_name_unique');
        });

        // 5. Add Postgres FK indexes on memberships for fast per-term & per-division listings.
        Schema::table('memberships', function (Blueprint $table) {
            $table->index('term_id', 'memberships_term_id_idx');
            $table->index('division_id', 'memberships_division_id_idx');
        });
    }

    public function down(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->dropIndex('memberships_term_id_idx');
            $table->dropIndex('memberships_division_id_idx');
        });

        Schema::table('divisions', function (Blueprint $table) {
            $table->dropUnique('divisions_org_term_name_unique');
            $table->unique(['organisation_id', 'name']);
            $table->dropIndex('divisions_term_id_idx');
            $table->dropConstrainedForeignId('term_id');
        });

        Schema::table('jobdescs', function (Blueprint $table) {
            $table->dropIndex('jobdescs_term_id_idx');
            $table->dropConstrainedForeignId('term_id');
        });

        Schema::table('organisations', function (Blueprint $table) {
            $table->dropIndex('organisations_advisor_user_id_idx');
            $table->dropConstrainedForeignId('advisor_user_id');
        });

        DB::table('users')->whereNull('password')->update(['password' => '']);

        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('activated_at');
            $table->string('password')->nullable(false)->change();
        });
    }
};
