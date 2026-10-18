<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('attendance')) {
            return;
        }

        Schema::table('attendance', function (Blueprint $table) {
            if (! Schema::hasColumn('attendance', 'student_id')) {
                $table->string('student_id')->nullable()->after('attendance_id');
            }

            if (! Schema::hasColumn('attendance', 'session_id')) {
                $table->string('session_id')->nullable()->after('student_id');
            }

            if (! Schema::hasColumn('attendance', 'status')) {
                $table->string('status')->default('Present')->after('session_id');
            }

            if (! Schema::hasColumn('attendance', 'scanned_at')) {
                $table->timestamp('scanned_at')->nullable()->after('status');
            }

            if (! Schema::hasColumn('attendance', 'date')) {
                $table->date('date')->nullable()->after('scanned_at');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('attendance')) {
            return;
        }

        Schema::table('attendance', function (Blueprint $table) {
            if (Schema::hasColumn('attendance', 'session_id')) {
                $table->dropColumn('session_id');
            }

            if (Schema::hasColumn('attendance', 'scanned_at')) {
                $table->dropColumn('scanned_at');
            }
        });
    }
};
