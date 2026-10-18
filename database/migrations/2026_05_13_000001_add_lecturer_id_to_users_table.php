<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('lecturer_id')->nullable()->after('role');
            $table->foreign('lecturer_id')
                  ->references('lecturer_id')
                  ->on('lecturer')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['lecturer_id']);
            $table->dropColumn('lecturer_id');
        });
    }
};
