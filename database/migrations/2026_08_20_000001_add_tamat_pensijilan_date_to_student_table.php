<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student', function (Blueprint $table) {
            $table->date('tamat_pensijilan_date')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('student', function (Blueprint $table) {
            $table->dropColumn('tamat_pensijilan_date');
        });
    }
};
