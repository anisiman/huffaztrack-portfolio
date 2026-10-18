<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Non-scanners become Absent as soon as a QR session expires,
// regardless of whether the lecturer reopens the attendance page.
Schedule::command('attendance:finalize-expired')->everyMinute()->withoutOverlapping();
