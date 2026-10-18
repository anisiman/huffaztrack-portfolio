<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasmi_progress', function (Blueprint $table) {

            $table->renameColumn(
                'page_reached',
                'pages_read_today'
            );

        });
    }

    public function down(): void
    {
        Schema::table('tasmi_progress', function (Blueprint $table) {

            $table->renameColumn(
                'pages_read_today',
                'page_reached'
            );

        });
    }
};