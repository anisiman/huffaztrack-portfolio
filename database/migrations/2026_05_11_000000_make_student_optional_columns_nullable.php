<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student', function (Blueprint $table) {
            $table->unsignedBigInteger('program_id')->nullable()->change();
            $table->unsignedBigInteger('batch_id')->nullable()->change();
            $table->string('gender')->nullable()->change();
            $table->string('nric')->nullable()->change();
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('student', function (Blueprint $table) {
            $table->unsignedBigInteger('program_id')->nullable(false)->change();
            $table->unsignedBigInteger('batch_id')->nullable(false)->change();
            $table->string('gender')->nullable(false)->change();
            $table->string('nric')->nullable(false)->change();
            $table->string('email')->nullable(false)->change();
        });
    }
};
