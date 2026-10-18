<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('muqarrar', function (Blueprint $table) {
            $table->unsignedBigInteger('batch_id')
                  ->nullable()
                  ->after('semester_id');

            $table->foreign('batch_id')
                  ->references('batch_id')
                  ->on('batch')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('muqarrar', function (Blueprint $table) {
            $table->dropForeign(['batch_id']);
            $table->dropColumn('batch_id');
        });
    }
};
