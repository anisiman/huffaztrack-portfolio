<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubjectAttendanceSession extends Model
{
    protected $fillable = [
        'session_id',
        'academic_period_id',
        'subjectT_id',
        'batch_id',
        'subject_section_id',
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

    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class, 'academic_period_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(SubjectT::class, 'subjectT_id', 'subjectT_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class, 'batch_id', 'batch_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(SubjectSection::class, 'subject_section_id');
    }

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_id', 'lecturer_id');
    }

    /**
     * Whether this session predates section-scoped attendance (subject_section_id
     * is null) for a Madah subject — i.e. a legacy batch-wide session. Never true
     * for Hafazan, which was never section-scoped in the first place.
     */
    public function isLegacyMadahSession(): bool
    {
        return $this->subject_section_id === null && $this->subject?->isMadah();
    }

    /** Human label for the session's roster scope — Section, Halaqah, or (legacy) Batch. */
    public function getScopeLabelAttribute(): string
    {
        if ($this->subject_section_id) {
            return 'Section ' . ($this->section?->section_name ?? '?');
        }

        if ($this->subject?->isHafazan()) {
            return 'Halaqah';
        }

        return $this->batch?->batch_name ?? '—';
    }

    public function records(): HasMany
    {
        return $this->hasMany(SubjectAttendanceRecord::class, 'subject_attendance_session_id');
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
