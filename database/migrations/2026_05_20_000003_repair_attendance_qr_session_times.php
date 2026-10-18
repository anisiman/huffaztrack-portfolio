<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('attendance_qr_sessions') || ! Schema::hasTable('attendance')) {
            return;
        }

        $sessions = DB::table('attendance_qr_sessions as sessions')
            ->join('attendance as attendance', 'attendance.session_id', '=', 'sessions.session_id')
            ->whereNotNull('attendance.scanned_at')
            ->groupBy('sessions.id', 'sessions.start_time')
            ->select('sessions.id', 'sessions.start_time', DB::raw('MIN(attendance.scanned_at) as first_scan'))
            ->get();

        foreach ($sessions as $session) {
            $firstScan = Carbon::parse($session->first_scan);
            $startTime = Carbon::parse($session->start_time);

            if ($firstScan->lt($startTime)) {
                DB::table('attendance_qr_sessions')
                    ->where('id', $session->id)
                    ->update([
                        'start_time' => $firstScan,
                        'end_time' => $firstScan->copy()->addHours(2),
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    public function down(): void
    {
        // Data repair only; the previous overwritten start time cannot be reconstructed safely.
    }
};
