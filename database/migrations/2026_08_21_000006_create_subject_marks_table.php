<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_marks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('academic_period_id')->constrained('academic_periods')->cascadeOnDelete();

            $table->foreignId('subjectT_id')->constrained('subjectt', 'subjectT_id')->cascadeOnDelete();

            $table->string('student_id');
            $table->foreign('student_id')->references('student_id')->on('student')->cascadeOnDelete();

            $table->unsignedBigInteger('lecturer_id')->nullable();
            $table->foreign('lecturer_id')->references('lecturer_id')->on('lecturer')->nullOnDelete();

            $table->decimal('carry_mark', 5, 2)->nullable();
            $table->decimal('final_exam_mark', 5, 2)->nullable();

            $table->timestamps();

            $table->unique(['academic_period_id', 'subjectT_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_marks');
    }
};
