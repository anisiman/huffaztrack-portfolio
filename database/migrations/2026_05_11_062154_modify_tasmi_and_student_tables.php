<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // REMOVE subject_id from tasmi
        Schema::table('tasmi', function (Blueprint $table) {

            $table->dropForeign(['subject_id']);

            $table->dropColumn('subject_id');
        });

        // ADD lecturer_id into student
        Schema::table('student', function (Blueprint $table) {

            $table->unsignedBigInteger('lecturer_id')->nullable();

            $table->foreign('lecturer_id')
                ->references('lecturer_id')
                ->on('lecturer');
        });
    }

    public function down(): void
    {
        // REMOVE lecturer_id from student
        Schema::table('student', function (Blueprint $table) {

            $table->dropForeign(['lecturer_id']);

            $table->dropColumn('lecturer_id');
        });

        // ADD BACK subject_id into tasmi
        Schema::table('tasmi', function (Blueprint $table) {

            $table->unsignedBigInteger('subject_id');

            $table->foreign('subject_id')
                ->references('subject_id')
                ->on('subjectt');
        });
    }
};