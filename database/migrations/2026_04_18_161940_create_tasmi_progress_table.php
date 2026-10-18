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
        Schema::create('tasmi_progress', function (Blueprint $table) {
        $table->id('progress_id');

        $table->unsignedBigInteger('tasmi_id');
        $table->foreign('tasmi_id')->references('tasmi_id')->on('tasmi');

        $table->integer('page_reached');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasmi_progress');
    }
};
