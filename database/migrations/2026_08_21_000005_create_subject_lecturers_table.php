<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_lecturers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('academic_period_id')->constrained('academic_periods')->cascadeOnDelete();

            $table->foreignId('subjectT_id')->constrained('subjectt', 'subjectT_id')->cascadeOnDelete();

            $table->unsignedBigInteger('lecturer_id');
            $table->foreign('lecturer_id')->references('lecturer_id')->on('lecturer')->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['academic_period_id', 'subjectT_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_lecturers');
    }
};
