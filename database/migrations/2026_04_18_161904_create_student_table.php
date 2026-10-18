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
        Schema::create('student', function (Blueprint $table) {
    $table->string('student_id')->primary(); // This allows "T-101" style IDs

    $table->unsignedBigInteger('program_id');
    $table->foreign('program_id')->references('program_id')->on('program');

    $table->unsignedBigInteger('sponsorship_id')->nullable();
    $table->foreign('sponsorship_id')->references('sponsorship_id')->on('sponsorship');

    $table->unsignedBigInteger('batch_id');
    $table->foreign('batch_id')->references('batch_id')->on('batch');

    $table->string('student_name');
    $table->string('gender');
    $table->string('nric');
    $table->string('email');
    $table->string('phone')->nullable();
    $table->string('address')->nullable();
    $table->string('town')->nullable();
    $table->string('state')->nullable();
    $table->string('postcode')->nullable();
    $table->string('secondary_school')->nullable();

    $table->timestamps();
    }); 
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student');
    }
};
