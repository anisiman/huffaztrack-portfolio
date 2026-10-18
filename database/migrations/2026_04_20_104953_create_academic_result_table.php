<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('academic_results', function (Blueprint $table) {
            $table->id('result_id');
            
            // Foreign Key to Student (Assuming your student PK is 'student_id')
            // If your student_id is a string/varchar, use $table->string('student_id');
            $table->string('student_id');
            $table->foreign('student_id')->references('student_id')->on('student')->onDelete('cascade');

            // Foreign Key to Program
            $table->unsignedBigInteger('program_id');
            $table->foreign('program_id')->references('program_id')->on('program');

            // Foreign Key to Semester
            $table->unsignedBigInteger('semester_id');
            $table->foreign('semester_id')->references('semester_id')->on('semester');

            // Result Data
            $table->decimal('gpa', 3, 2);  // Stores up to 9.99 (e.g., 3.75)
            $table->decimal('cgpa', 3, 2); // Stores up to 9.99
            
            $table->timestamps(); // Highly recommended for tracking when results were updated
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_results');
    }
};