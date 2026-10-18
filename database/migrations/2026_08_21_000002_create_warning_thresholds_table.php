<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warning_thresholds', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('academic_period_id')->unique();
            $table->foreign('academic_period_id')->references('id')->on('academic_periods')->cascadeOnDelete();

            $table->unsignedInteger('warning_1_threshold');
            $table->unsignedInteger('warning_2_threshold');
            $table->unsignedInteger('warning_3_threshold');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warning_thresholds');
    }
};
