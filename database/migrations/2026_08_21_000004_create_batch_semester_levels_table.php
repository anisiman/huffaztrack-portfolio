<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_semester_levels', function (Blueprint $table) {
            $table->id();

            $table->foreignId('academic_period_id')->constrained('academic_periods')->cascadeOnDelete();

            $table->unsignedBigInteger('batch_id');
            $table->foreign('batch_id')->references('batch_id')->on('batch')->cascadeOnDelete();

            $table->unsignedBigInteger('semester_id');
            $table->foreign('semester_id')->references('semester_id')->on('semester')->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['academic_period_id', 'batch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_semester_levels');
    }
};
