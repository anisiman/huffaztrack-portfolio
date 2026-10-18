<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sponsorship', function (Blueprint $table) {
            $table->string('sponsorship_duration')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('sponsorship', function (Blueprint $table) {
            $table->string('sponsorship_duration')->nullable(false)->change();
        });
    }
};