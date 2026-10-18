<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('khatam_records', function (Blueprint $table) {
            $table->id();
            $table->string('student_id');
            $table->foreign('student_id')->references('student_id')->on('student')->cascadeOnDelete();
            $table->date('khatam_date');
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'khatam_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('khatam_records');
    }
};
