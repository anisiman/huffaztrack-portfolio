<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_subject_sections', function (Blueprint $table) {
            $table->id();

            $table->string('student_id');
            $table->foreign('student_id')->references('student_id')->on('student')->cascadeOnDelete();

            // Denormalized from subject_section at enrollment time so the
            // "one section per subject per semester" rule can be enforced with
            // a plain unique index, without joining through subject_sections.
            $table->foreignId('subjectT_id')->constrained('subjectt', 'subjectT_id')->cascadeOnDelete();
            $table->foreignId('academic_period_id')->constrained('academic_periods')->cascadeOnDelete();

            $table->foreignId('subject_section_id')->constrained('subject_sections')->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['student_id', 'subjectT_id', 'academic_period_id'], 'student_subject_sections_unique_enrollment');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_subject_sections');
    }
};
