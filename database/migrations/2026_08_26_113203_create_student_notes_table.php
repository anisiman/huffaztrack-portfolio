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
        Schema::create('student_notes', function (Blueprint $table) {
            $table->id();
            $table->string('student_id');
            $table->foreign('student_id')->references('student_id')->on('student')->cascadeOnDelete();
            $table->unsignedBigInteger('lecturer_id');
            $table->foreign('lecturer_id')->references('lecturer_id')->on('lecturer')->cascadeOnDelete();
            $table->text('body');
            $table->boolean('is_private')->default(false);
            $table->timestamps();

            $table->index(['student_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_notes');
    }
};
