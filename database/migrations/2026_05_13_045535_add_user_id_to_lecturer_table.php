<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lecturer', function (Blueprint $table) {

            // add user_id column
            $table->unsignedBigInteger('user_id')
                  ->nullable()
                  ->after('lecturer_name');

            // foreign key
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lecturer', function (Blueprint $table) {

            // drop foreign key first
            $table->dropForeign(['user_id']);

            // drop column
            $table->dropColumn('user_id');

        });
    }
};