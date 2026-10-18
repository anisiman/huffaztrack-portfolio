<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceQrSession extends Model
{
    protected $table = 'attendance_qr_sessions';

    protected $fillable = [
        'session_id',
        'lecturer_id',
        'start_time',
        'end_time',
        'ended_at',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
        'ended_at'   => 'datetime',
        'finalized_at' => 'datetime',
    ];

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_id', 'lecturer_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'session_id', 'session_id');
    }

    public function isExpired(): bool
    {
        return now()->greaterThan($this->end_time);
    }

    public function isManuallyPaused(): bool
    {
        return $this->ended_at !== null && ! $this->isExpired();
    }

    public function isActive(): bool
    {
        return ! $this->isExpired() && $this->ended_at === null;
    }

    public function hasEnded(): bool
    {
        return $this->isExpired() || $this->isManuallyPaused();
    }
}
