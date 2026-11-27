<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proker_division_jobdescs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proker_division_id')->constrained()->cascadeOnDelete();
            $table->string('title', 120);
            // Days before Hari-H; negative values mean days after Hari-H (e.g. -2 = H+2).
            $table->integer('days_before');
            $table->boolean('done')->default(false);
            $table->timestamps();

            $table->index('proker_division_id', 'proker_division_jobdescs_division_id_idx');
        });

        Schema::create('proker_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proker_id')->constrained()->cascadeOnDelete();
            // pleno, koor, or division
            $table->string('scope', 20);
            $table->foreignId('proker_division_id')->nullable()->constrained()->cascadeOnDelete();
            // 0 = Minggu ... 6 = Sabtu
            $table->unsignedTinyInteger('weekday');
            $table->time('starts_at');
            $table->string('place', 80)->nullable();
            $table->timestamps();

            $table->index(['proker_id', 'weekday', 'starts_at'], 'proker_meetings_proker_schedule_idx');
            $table->index('proker_division_id', 'proker_meetings_division_id_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proker_meetings');
        Schema::dropIfExists('proker_division_jobdescs');
    }
};
