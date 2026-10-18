<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_attendance_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('subject_attendance_session_id')
                ->constrained('subject_attendance_sessions')
                ->cascadeOnDelete();

            $table->string('student_id');
            $table->foreign('student_id')->references('student_id')->on('student')->cascadeOnDelete();

            // Denormalized from the session at creation time — same rationale as SubjectMark:
            // single-table filtering for the report's 5 filter dimensions, and historical
            // correctness if a student's batch is later reassigned.
            $table->foreignId('academic_period_id')->constrained('academic_periods')->cascadeOnDelete();
            $table->foreignId('subjectT_id')->constrained('subjectt', 'subjectT_id')->cascadeOnDelete();
            $table->foreignId('batch_id')->constrained('batch', 'batch_id')->cascadeOnDelete();
            $table->foreignId('lecturer_id')->constrained('lecturer', 'lecturer_id')->cascadeOnDelete();

            $table->string('status')->default('present'); // present | absent
            $table->timestamp('scanned_at')->nullable();
            $table->date('date');

            $table->timestamps();

            $table->unique(['subject_attendance_session_id', 'student_id'], 'sar_session_student_unique');
            $table->index(['academic_period_id', 'subjectT_id', 'batch_id'], 'sar_period_subject_batch_index');
            $table->index(['lecturer_id', 'date'], 'sar_lecturer_date_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_attendance_records');
    }
};
