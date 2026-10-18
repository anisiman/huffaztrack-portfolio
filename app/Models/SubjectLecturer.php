<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubjectLecturer extends Model
{
    protected $fillable = [
        'academic_period_id',
        'subjectT_id',
        'lecturer_id',
    ];

    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class, 'academic_period_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(SubjectT::class, 'subjectT_id', 'subjectT_id');
    }

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_id', 'lecturer_id');
    }
}
