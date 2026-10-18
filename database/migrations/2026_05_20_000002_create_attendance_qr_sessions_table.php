<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_qr_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();
            $table->unsignedBigInteger('lecturer_id');
            $table->timestamp('start_time');
            $table->timestamp('end_time');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->foreign('lecturer_id')
                ->references('lecturer_id')
                ->on('lecturer')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_qr_sessions');
    }
};
