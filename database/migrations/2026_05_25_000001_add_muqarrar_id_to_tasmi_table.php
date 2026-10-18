<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('tasmi', 'muqarrar_id')) {
            return;
        }

        Schema::table('tasmi', function (Blueprint $table) {
            $table->unsignedBigInteger('muqarrar_id')
                  ->nullable()
                  ->after('lecturer_id');

            $table->foreign('muqarrar_id')
                  ->references('muqarrar_id')
                  ->on('muqarrar')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('tasmi', 'muqarrar_id')) {
            return;
        }

        Schema::table('tasmi', function (Blueprint $table) {
            $table->dropForeign(['muqarrar_id']);
            $table->dropColumn('muqarrar_id');
        });
    }
};
