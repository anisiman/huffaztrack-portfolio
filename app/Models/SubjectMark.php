<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubjectMark extends Model
{
    protected $fillable = [
        'academic_period_id',
        'subjectT_id',
        'student_id',
        'lecturer_id',
        'carry_mark_1',
        'carry_mark_2',
    ];

    protected $casts = [
        'carry_mark_1' => 'float',
        'carry_mark_2' => 'float',
    ];

    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class, 'academic_period_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(SubjectT::class, 'subjectT_id', 'subjectT_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_id', 'lecturer_id');
    }

    /** Total Carry Marks = CM1 + CM2 (null-safe: treats a missing mark as 0 only when the other is present). */
    public function getTotalAttribute(): ?float
    {
        if ($this->carry_mark_1 === null && $this->carry_mark_2 === null) {
            return null;
        }

        return (float) $this->carry_mark_1 + (float) $this->carry_mark_2;
    }
}
