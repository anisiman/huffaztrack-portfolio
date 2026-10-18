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
        Schema::create('warning_letter', function (Blueprint $table) {

            $table->id('warning_id');

            // FK to student
            $table->string('student_id');

            $table->foreign('student_id')
                  ->references('student_id')
                  ->on('student')
                  ->onDelete('cascade');

            // FK to attendance
            $table->unsignedBigInteger('attendance_id');

            $table->foreign('attendance_id')
                  ->references('attendance_id')
                  ->on('attendance')
                  ->onDelete('cascade');

            // warning details
            $table->integer('attendance_count');

            $table->integer('warning_level');

            $table->date('letter_date');

            $table->string('status')->default('active');

            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warning_letter');
    }
};