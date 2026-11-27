<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prokers', function (Blueprint $table) {
            $table->string('location', 120)->nullable()->after('ends_on');
            $table->index('previous_proker_id', 'prokers_previous_proker_id_idx');
            $table->index('status', 'prokers_status_idx');
        });

        Schema::table('proker_divisions', function (Blueprint $table) {
            $table->unique(['proker_id', 'name'], 'proker_divisions_proker_name_unique');
            $table->index('division_id', 'proker_divisions_division_id_idx');
        });

        Schema::table('proker_assignments', function (Blueprint $table) {
            // Enforces "one person, one role per proker" even when proker_division_id is NULL.
            $table->unique(['proker_id', 'membership_id'], 'proker_assignments_one_role_per_proker');
            $table->index('membership_id', 'proker_assignments_membership_id_idx');
            $table->index('proker_division_id', 'proker_assignments_proker_division_id_idx');
            $table->index('reports_to_id', 'proker_assignments_reports_to_id_idx');
        });
    }

    public function down(): void
    {
        Schema::table('proker_assignments', function (Blueprint $table) {
            $table->dropUnique('proker_assignments_one_role_per_proker');
            $table->dropIndex('proker_assignments_membership_id_idx');
            $table->dropIndex('proker_assignments_proker_division_id_idx');
            $table->dropIndex('proker_assignments_reports_to_id_idx');
        });

        Schema::table('proker_divisions', function (Blueprint $table) {
            $table->dropUnique('proker_divisions_proker_name_unique');
            $table->dropIndex('proker_divisions_division_id_idx');
        });

        Schema::table('prokers', function (Blueprint $table) {
            $table->dropIndex('prokers_previous_proker_id_idx');
            $table->dropIndex('prokers_status_idx');
            $table->dropColumn('location');
        });
    }
};
