<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubjectSection extends Model
{
    protected $fillable = [
        'subjectT_id',
        'academic_period_id',
        'section_name',
        'lecturer_id',
        'capacity',
    ];

    protected $casts = [
        'capacity' => 'integer',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(SubjectT::class, 'subjectT_id', 'subjectT_id');
    }

    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class, 'academic_period_id');
    }

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_id', 'lecturer_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentSubjectSection::class, 'subject_section_id');
    }

    public function attendanceSessions(): HasMany
    {
        return $this->hasMany(SubjectAttendanceSession::class, 'subject_section_id');
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(SubjectAttendanceRecord::class, 'subject_section_id');
    }

    /** Enrolled count from a loaded relation when available, else a fresh query. */
    public function enrolledCount(): int
    {
        return $this->relationLoaded('enrollments') ? $this->enrollments->count() : $this->enrollments()->count();
    }

    public function remainingSeats(): int
    {
        return max(0, $this->capacity - $this->enrolledCount());
    }

    public function isFull(): bool
    {
        return $this->enrolledCount() >= $this->capacity;
    }
}
