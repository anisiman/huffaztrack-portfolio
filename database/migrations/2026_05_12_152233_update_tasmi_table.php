<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasmi', function (Blueprint $table) {

            // drop old foreign key first
            $table->dropForeign(['semester_id']);

            // remove unnecessary columns
            $table->dropColumn([
                'semester_id',
                'week',
                'target_page'
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('tasmi', function (Blueprint $table) {

            // restore removed columns
            $table->unsignedBigInteger('semester_id')->nullable();
            $table->integer('week')->nullable();
            $table->string('target_page')->nullable();

            // restore semester foreign key
            $table->foreign('semester_id')
                  ->references('semester_id')
                  ->on('semester')
                  ->onDelete('cascade');
        });
    }
};