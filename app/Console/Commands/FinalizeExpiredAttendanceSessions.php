<?php

namespace App\Console\Commands;

use App\Models\AttendanceQrSession;
use App\Models\SubjectAttendanceSession;
use App\Services\AttendanceFinalizer;
use Illuminate\Console\Command;

class FinalizeExpiredAttendanceSessions extends Command
{
    protected $signature = 'attendance:finalize-expired';

    protected $description = 'Mark students who did not scan as Absent for every QR attendance session whose time has run out';

    public function handle(AttendanceFinalizer $finalizer): int
    {
        $tasmik = AttendanceQrSession::whereNull('finalized_at')
            ->where('end_time', '<', now())
            ->get();

        foreach ($tasmik as $session) {
            $finalizer->finalizeTasmik($session);
        }

        $subject = SubjectAttendanceSession::whereNull('finalized_at')
            ->where('end_time', '<', now())
            ->get();

        foreach ($subject as $session) {
            $finalizer->finalizeSubject($session);
        }

        $this->info("Finalized {$tasmik->count()} Tasmik and {$subject->count()} Subject session(s).");

        return self::SUCCESS;
    }
}
