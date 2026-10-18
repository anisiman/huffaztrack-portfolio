<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_appeals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attendance_id');
            $table->string('student_id');
            $table->string('lecturer_id');
            $table->date('date');
            $table->string('reason_type')->nullable();
            $table->text('remarks')->nullable();
            $table->string('document_path');
            $table->string('status')->default('pending'); // pending | approved | rejected
            $table->text('rejection_remarks')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->foreign('attendance_id')
                  ->references('attendance_id')
                  ->on('attendance')
                  ->onDelete('cascade');

            // One appeal per attendance record
            $table->unique('attendance_id');
            $table->index(['lecturer_id', 'status']);
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_appeals');
    }
};
