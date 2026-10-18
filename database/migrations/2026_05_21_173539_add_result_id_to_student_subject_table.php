<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_subject', function (Blueprint $table) {

            $table->unsignedBigInteger('result_id')->nullable()->after('student_id');

            $table->foreign('result_id')
                ->references('result_id')
                ->on('result')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('student_subject', function (Blueprint $table) {

            $table->dropForeign(['result_id']);
            $table->dropColumn('result_id');
        });
    }
};
