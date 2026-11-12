<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nim')->nullable()->unique()->after('email');
            $table->string('phone')->nullable()->after('nim');
        });

        Schema::create('organisations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type'); // himpunan, bem, mentoring, bpm, ukm
            $table->string('faculty')->nullable();
            $table->timestamps();
        });

        // Kepengurusan period, so "last term's prokers" stay traceable.
        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g. 2026/2027
            $table->date('starts_on');
            $table->date('ends_on');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('divisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();

            $table->unique(['organisation_id', 'name']);
        });

        Schema::create('jobdescs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('division_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('level'); // dosen, hod, koordinator, staff
            $table->timestamps();
        });

        // A person's standing position in one organisation for one term.
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organisation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->constrained()->cascadeOnDelete();
            $table->foreignId('division_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('jobdesc_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('active'); // active, intern, alumni
            $table->timestamps();

            $table->unique(['user_id', 'organisation_id', 'term_id']);
        });

        // Pulse talent pipeline.
        Schema::create('interns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membership_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('mentor_membership_id')->nullable()->constrained('memberships')->nullOnDelete();
            $table->string('cohort');
            $table->unsignedTinyInteger('score')->nullable();
            $table->string('status')->default('active'); // active, passed, dropped
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interns');
        Schema::dropIfExists('memberships');
        Schema::dropIfExists('jobdescs');
        Schema::dropIfExists('divisions');
        Schema::dropIfExists('terms');
        Schema::dropIfExists('organisations');

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['nim']); // SQLite can't drop an indexed column
            $table->dropColumn(['nim', 'phone']);
        });
    }
};
