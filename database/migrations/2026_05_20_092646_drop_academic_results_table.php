<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Menggugurkan jadual jika ia wujud
        Schema::dropIfExists('academic_results');
    }

    public function down(): void
    {
        // Struktur asal sekiranya anda melakukan rollback (optional)
        Schema::create('academic_results', function (Blueprint $table) {
            $table->id('academic_result_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('semester_id');
            $table->decimal('gpa', 4, 2)->nullable();
            $table->decimal('cgpa', 4, 2)->nullable();
            $table->timestamps();
        });
    }
};