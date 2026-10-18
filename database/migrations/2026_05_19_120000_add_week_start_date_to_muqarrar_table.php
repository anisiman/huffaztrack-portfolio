<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('muqarrar', function (Blueprint $table) {
            $table->date('week_start_date')->nullable()->after('week');
        });
    }

    public function down(): void
    {
        Schema::table('muqarrar', function (Blueprint $table) {
            $table->dropColumn('week_start_date');
        });
    }
};
