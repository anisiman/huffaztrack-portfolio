<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('attendance') || ! Schema::hasColumn('attendance', 'tasmi_id')) {
            return;
        }

        Schema::table('attendance', function (Blueprint $table) {
            $table->dropForeign(['tasmi_id']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE attendance MODIFY tasmi_id BIGINT UNSIGNED NULL');
        } else {
            Schema::table('attendance', function (Blueprint $table) {
                $table->unsignedBigInteger('tasmi_id')->nullable()->change();
            });
        }

        Schema::table('attendance', function (Blueprint $table) {
            $table->foreign('tasmi_id')
                ->references('tasmi_id')
                ->on('tasmi')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('attendance') || ! Schema::hasColumn('attendance', 'tasmi_id')) {
            return;
        }

        Schema::table('attendance', function (Blueprint $table) {
            $table->dropForeign(['tasmi_id']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE attendance MODIFY tasmi_id BIGINT UNSIGNED NOT NULL');
        } else {
            Schema::table('attendance', function (Blueprint $table) {
                $table->unsignedBigInteger('tasmi_id')->nullable(false)->change();
            });
        }

        Schema::table('attendance', function (Blueprint $table) {
            $table->foreign('tasmi_id')
                ->references('tasmi_id')
                ->on('tasmi');
        });
    }
};
