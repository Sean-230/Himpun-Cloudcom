<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('division_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proker_id')->constrained('prokers')->cascadeOnDelete();
            $table->foreignId('from_proker_division_id')->constrained('proker_divisions')->cascadeOnDelete();
            $table->foreignId('to_proker_division_id')->constrained('proker_divisions')->cascadeOnDelete();
            $table->foreignId('from_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('to_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 150);
            $table->text('note')->nullable();
            $table->date('due_on');
            $table->string('status')->default('Menunggu'); // Menunggu, Diproses, Selesai, Ditolak
            $table->timestamps();

            $table->index(['proker_id', 'status']);
            $table->index('to_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('division_requests');
    }
};