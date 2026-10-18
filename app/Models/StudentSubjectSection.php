<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentSubjectSection extends Model
{
    protected $fillable = [
        'student_id',
        'subjectT_id',
        'academic_period_id',
        'subject_section_id',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(SubjectT::class, 'subjectT_id', 'subjectT_id');
    }

    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class, 'academic_period_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(SubjectSection::class, 'subject_section_id');
    }
}
