<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oprec_vacancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proker_id')->constrained()->cascadeOnDelete();
            $table->foreignId('proker_division_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->unsignedSmallInteger('quota')->default(1);
            $table->text('requirements')->nullable();
            $table->dateTime('opens_at')->nullable();
            $table->dateTime('closes_at')->nullable();
            $table->string('status')->default('draft'); // draft, open, closed
            $table->timestamps();
        });

        Schema::create('oprec_applicants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('oprec_vacancy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // applied, screening, interview, accepted, rejected
            $table->string('status')->default('applied');
            $table->text('notes')->nullable();
            // Filled when the applicant is inducted into the committee.
            $table->foreignId('proker_assignment_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['oprec_vacancy_id', 'user_id']);
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proker_id')->constrained()->cascadeOnDelete();
            $table->foreignId('proker_division_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('proker_assignments')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('due_on')->nullable();
            $table->string('priority')->default('normal');
            $table->string('status')->default('todo'); // todo, doing, in_review, done
            $table->timestamps();
        });

        Schema::create('task_assignees', function (Blueprint $table) {
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('proker_assignment_id')->constrained()->cascadeOnDelete();

            $table->primary(['task_id', 'proker_assignment_id']);
        });

        // Enforces "no skipping levels": steps are approved in order.
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->morphs('approvable');
            $table->unsignedTinyInteger('step');
            $table->string('required_role'); // koordinator, hod_pj, ketupel
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->foreignId('acted_by')->nullable()->constrained('proker_assignments')->nullOnDelete();
            $table->timestamp('acted_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proker_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // proposal, lpj, other
            $table->string('title');
            $table->string('path');
            $table->string('status')->default('draft');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
        Schema::dropIfExists('approvals');
        Schema::dropIfExists('task_assignees');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('oprec_applicants');
        Schema::dropIfExists('oprec_vacancies');
    }
};
