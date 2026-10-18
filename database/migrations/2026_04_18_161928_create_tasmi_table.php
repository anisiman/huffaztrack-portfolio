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
        Schema::create('tasmi', function (Blueprint $table) {
    $table->id('tasmi_id');

    $table->string('student_id');
    $table->foreign('student_id')->references('student_id')->on('student')->onDelete('cascade');

    $table->unsignedBigInteger('lecturer_id');
    $table->foreign('lecturer_id')->references('lecturer_id')->on('lecturer');

    $table->unsignedBigInteger('subject_id');
    $table->foreign('subject_id')->references('subjectT_id')->on('subjectt');

    $table->unsignedBigInteger('semester_id');
    $table->foreign('semester_id')->references('semester_id')->on('semester');

    $table->integer('week');
    $table->integer('target_page');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasmi');
    }
};
