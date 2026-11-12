<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prokers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->constrained()->cascadeOnDelete();
            // Links a "repeat" proker to the one it continues from a past term.
            $table->foreignId('previous_proker_id')->nullable()->constrained('prokers')->nullOnDelete();
            $table->string('name');
            $table->string('type'); // repeat, new
            // proposed, ratified, committee_formed, running, lpj, archived, disbanded
            $table->string('status')->default('proposed');
            $table->text('description')->nullable();
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->timestamps();

            $table->index(['organisation_id', 'term_id']);
        });

        // Committee divisions are chosen per proker by the Ketupel.
        Schema::create('proker_divisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proker_id')->constrained()->cascadeOnDelete();
            $table->foreignId('division_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        // Core table: one person can hold many rows across many prokers.
        Schema::create('proker_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proker_id')->constrained()->cascadeOnDelete();
            $table->foreignId('membership_id')->constrained()->cascadeOnDelete();
            $table->foreignId('proker_division_id')->nullable()->constrained()->nullOnDelete();
            $table->string('role'); // ketupel, pj, hod, koordinator, member
            // Reporting chain: Member -> Koordinator -> HOD/PJ -> Ketupel.
            $table->foreignId('reports_to_id')->nullable()->constrained('proker_assignments')->nullOnDelete();
            $table->timestamps();

            $table->unique(['proker_id', 'membership_id', 'proker_division_id', 'role'], 'proker_assignments_unique');
        });

        Schema::create('budget_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proker_id')->constrained()->cascadeOnDelete();
            $table->foreignId('proker_division_id')->nullable()->constrained()->nullOnDelete();
            $table->string('kind'); // planned (proposal), actual (LPJ)
            $table->string('type'); // income, expense
            $table->string('category');
            $table->string('description')->nullable();
            $table->unsignedBigInteger('amount'); // whole rupiah, no decimals
            $table->date('entry_date')->nullable();
            $table->string('receipt_path')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['proker_id', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_entries');
        Schema::dropIfExists('proker_assignments');
        Schema::dropIfExists('proker_divisions');
        Schema::dropIfExists('prokers');
    }
};
