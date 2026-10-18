<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubjectAttendanceRecord extends Model
{
    protected $fillable = [
        'subject_attendance_session_id',
        'student_id',
        'academic_period_id',
        'subjectT_id',
        'batch_id',
        'subject_section_id',
        'lecturer_id',
        'status',
        'scanned_at',
        'date',
    ];

    protected $casts = [
        'date'       => 'date',
        'scanned_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(SubjectAttendanceSession::class, 'subject_attendance_session_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

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
}
