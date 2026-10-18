<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjectt', function (Blueprint $table) {
            $table->boolean('has_final_exam')->default(true)->after('credit_hours');
        });
    }

    public function down(): void
    {
        Schema::table('subjectt', function (Blueprint $table) {
            $table->dropColumn('has_final_exam');
        });
    }
};
