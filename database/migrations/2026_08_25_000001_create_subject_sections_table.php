<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_sections', function (Blueprint $table) {
            $table->id();

            $table->foreignId('subjectT_id')->constrained('subjectt', 'subjectT_id')->cascadeOnDelete();
            $table->foreignId('academic_period_id')->constrained('academic_periods')->cascadeOnDelete();

            $table->string('section_name');

            $table->unsignedBigInteger('lecturer_id')->nullable();
            $table->foreign('lecturer_id')->references('lecturer_id')->on('lecturer')->nullOnDelete();

            $table->unsignedInteger('capacity');

            $table->timestamps();

            $table->unique(['subjectT_id', 'academic_period_id', 'section_name'], 'subject_sections_unique_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_sections');
    }
};
