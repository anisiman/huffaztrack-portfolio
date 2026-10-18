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
        Schema::create('muqarrar', function (Blueprint $table) {
        $table->id('muqarrar_id');

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
        Schema::dropIfExists('muqarrar');
    }
};
