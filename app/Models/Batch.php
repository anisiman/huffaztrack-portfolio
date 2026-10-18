<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    protected $table = 'batch';
    protected $primaryKey = 'batch_id';

    protected $fillable = [
        'batch_name',
        'intake',
        'is_active',
        'auto_completed_students',
        'date_registered'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'auto_completed_students' => 'array',
    ];

    public $timestamps = false;

    /** Batches currently flagged active in Batch Management — the single source of truth for "active batches" app-wide. */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Natural numeric order by batch name (T1, T2, ... T10, T11) instead of lexical ("T10" < "T2"). */
    public function scopeOrderedByName($query)
    {
        return $query->orderByRaw('CAST(SUBSTRING(batch_name, 2) AS UNSIGNED) ASC');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'batch_id', 'batch_id');
    }

    public function batchSemesterLevels()
    {
        return $this->hasMany(BatchSemesterLevel::class, 'batch_id', 'batch_id');
    }

    public function subjectAttendanceSessions()
    {
        return $this->hasMany(SubjectAttendanceSession::class, 'batch_id', 'batch_id');
    }

    /** This batch's Semester Level (1-7) for a given Academic Semester, if mapped. */
    public function semesterLevelFor(AcademicPeriod $period): ?Semester
    {
        return BatchSemesterLevel::with('semester')
            ->where('batch_id', $this->batch_id)
            ->where('academic_period_id', $period->id)
            ->first()?->semester;
    }
}