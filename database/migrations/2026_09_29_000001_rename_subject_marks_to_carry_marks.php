<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Final exam marks are handled by the higher institution, not recorded here.
 * Marks are now two carry-mark components; Total Carry Marks = CM1 + CM2.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subject_marks', function (Blueprint $table) {
            $table->renameColumn('carry_mark', 'carry_mark_1');
            $table->renameColumn('final_exam_mark', 'carry_mark_2');
        });
    }

    public function down(): void
    {
        Schema::table('subject_marks', function (Blueprint $table) {
            $table->renameColumn('carry_mark_1', 'carry_mark');
            $table->renameColumn('carry_mark_2', 'final_exam_mark');
        });
    }
};
