<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_programs', function (Blueprint $table) {
            $table->id();
            
            // The Main Identity (Permanent Tahfiz ID)
            $table->string('student_id');
            $table->foreign('student_id')->references('student_id')->on('student')->onDelete('cascade');

            // The Program being taken
            $table->unsignedBigInteger('program_id');
            $table->foreign('program_id')->references('program_id')->on('program')->onDelete('cascade');

            // Program-Specific ID (e.g., ID Asasi or ID Ijazah)
            // We use nullable() in case a program doesn't have a specific external ID yet
            $table->string('enrollment_id')->nullable(); 

            // The "Tracking" Status
            // Examples: 'Active', 'Finished', 'Dropped', 'Switching'
            $table->string('status')->default('Active');

            // Dates for history tracking
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_programs');
    }
};