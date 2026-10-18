<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('batch', function (Blueprint $table) {
            $table->date('date_registered')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('batch', function (Blueprint $table) {
            $table->date('date_registered')->nullable(false)->change();
        });
    }
};