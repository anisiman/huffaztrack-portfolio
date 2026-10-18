<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('student', 'semester_id')) {
            Schema::table('student', function (Blueprint $table) {
                $table->string('semester_id', 50)->nullable()->after('batch_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('student', 'semester_id')) {
            Schema::table('student', function (Blueprint $table) {
                $table->dropColumn('semester_id');
            });
        }
    }
};
