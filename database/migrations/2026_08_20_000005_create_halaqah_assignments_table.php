<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('halaqah_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('academic_period_id')->constrained('academic_periods')->cascadeOnDelete();

            // student_id/lecturer_id are nullOnDelete rather than cascade: if the
            // underlying student/lecturer record is later deleted or renamed, the
            // historical row (and its frozen name snapshots below) must survive.
            $table->string('student_id')->nullable();
            $table->foreign('student_id')->references('student_id')->on('student')->nullOnDelete();

            $table->unsignedBigInteger('lecturer_id')->nullable();
            $table->foreign('lecturer_id')->references('lecturer_id')->on('lecturer')->nullOnDelete();

            $table->string('student_name_snapshot');
            $table->string('lecturer_name_snapshot');

            $table->timestamps();

            $table->unique(['academic_period_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('halaqah_assignments');
    }
};
