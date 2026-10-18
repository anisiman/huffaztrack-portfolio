<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('muqarrar', function (Blueprint $table) {
            $table->unsignedBigInteger('academic_period_id')->nullable()->after('batch_id');
            $table->foreign('academic_period_id')->references('id')->on('academic_periods')->nullOnDelete();
        });

        // semester_id used to be the level (1-7) an admin picked manually; new Muqarrar
        // records now derive their period from academic_period_id instead, so it must
        // become optional. doctrine/dbal isn't installed, so ->change() isn't available.
        DB::statement('ALTER TABLE muqarrar MODIFY semester_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        Schema::table('muqarrar', function (Blueprint $table) {
            $table->dropForeign(['academic_period_id']);
            $table->dropColumn('academic_period_id');
        });

        DB::statement('ALTER TABLE muqarrar MODIFY semester_id BIGINT UNSIGNED NOT NULL');
    }
};
