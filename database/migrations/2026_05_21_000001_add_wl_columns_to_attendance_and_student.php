<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add excused-absence fields to attendance
        Schema::table('attendance', function (Blueprint $table) {
            if (! Schema::hasColumn('attendance', 'reason_type')) {
                $table->string('reason_type')->nullable()->after('status');
            }
            if (! Schema::hasColumn('attendance', 'remarks')) {
                $table->text('remarks')->nullable()->after('reason_type');
            }
        });

        // Add warning tracking to student
        Schema::table('student', function (Blueprint $table) {
            if (! Schema::hasColumn('student', 'warning_level')) {
                $table->unsignedTinyInteger('warning_level')->default(0)->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendance', function (Blueprint $table) {
            $table->dropColumn(['reason_type', 'remarks']);
        });

        Schema::table('student', function (Blueprint $table) {
            $table->dropColumn('warning_level');
        });
    }
};
