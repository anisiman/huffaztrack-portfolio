<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * finalized_at marks a QR session whose non-scanners have been marked Absent
 * (see App\Services\AttendanceFinalizer). Sessions that had already expired
 * before this migration are stamped as finalized so the scheduler only acts
 * on sessions from now on and does not retroactively mark past days absent.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['attendance_qr_sessions', 'subject_attendance_sessions'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->timestamp('finalized_at')->nullable()->after('ended_at');
            });

            DB::table($table)
                ->where('end_time', '<', now())
                ->update(['finalized_at' => DB::raw('end_time')]);
        }
    }

    public function down(): void
    {
        foreach (['attendance_qr_sessions', 'subject_attendance_sessions'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn('finalized_at');
            });
        }
    }
};
