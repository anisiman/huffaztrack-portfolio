<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('academic_year');
            $table->unsignedTinyInteger('period_number');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['upcoming', 'active', 'completed'])->default('upcoming');
            $table->boolean('status_locked')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['academic_year', 'period_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_periods');
    }
};
