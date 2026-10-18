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
        Schema::create('attendance', function (Blueprint $table) {
        $table->id('attendance_id');

        $table->date('date');
        $table->string('status');

        $table->unsignedBigInteger('tasmi_id')->nullable();
        $table->foreign('tasmi_id')->references('tasmi_id')->on('tasmi')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
