<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('muqarrar', function (Blueprint $table) {

            // rename target_page → end_page
            $table->renameColumn('target_page', 'end_page');

        });

        Schema::table('muqarrar', function (Blueprint $table) {

            // add start_page
            $table->integer('start_page')
                  ->after('week');

        });
    }

    public function down(): void
    {
        Schema::table('muqarrar', function (Blueprint $table) {

            // remove start_page
            $table->dropColumn('start_page');

            // rename back
            $table->renameColumn('end_page', 'target_page');

        });
    }
};