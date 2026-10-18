<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjectt', function (Blueprint $table) {
            // hafazan: uses the existing Halaqah lecturer-student assignment, no sections.
            // madah: academic subjects with lecturer-per-section + student self-enrollment.
            $table->string('type')->default('madah')->after('subjectT_name');
        });

        // Backfill: every existing "HIFZ AL-QURAN ..." subject is Hafazan; everything else is Madah.
        DB::table('subjectt')->where('subjectT_name', 'like', 'HIFZ%')->update(['type' => 'hafazan']);
    }

    public function down(): void
    {
        Schema::table('subjectt', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
