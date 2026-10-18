<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_attendance_sessions', function (Blueprint $table) {
            $table->id();

            // Distinct prefix ('SATT-') from Tasmik's attendance_qr_sessions ('ATT-') so
            // scan URLs/logs from the two attendance systems never collide.
            $table->string('session_id')->unique();

            $table->foreignId('academic_period_id')->constrained('academic_periods')->cascadeOnDelete();
            $table->foreignId('subjectT_id')->constrained('subjectt', 'subjectT_id')->cascadeOnDelete();
            $table->foreignId('batch_id')->constrained('batch', 'batch_id')->cascadeOnDelete();
            $table->foreignId('lecturer_id')->constrained('lecturer', 'lecturer_id')->cascadeOnDelete();

            $table->timestamp('start_time');
            $table->timestamp('end_time');
            $table->timestamp('ended_at')->nullable();

            $table->timestamps();

            $table->index(['lecturer_id', 'start_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_attendance_sessions');
    }
};
