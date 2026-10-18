<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentSubject extends Model
{
    protected $table = 'student_subject';
    protected $primaryKey = 'student_subject_id';
    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'subjectT_id',
        'result_id',
        'grade',
        'grade_point'
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function subjectT(): BelongsTo
    {
        return $this->belongsTo(SubjectT::class, 'subjectT_id', 'subjectT_id');
    }

    public function result(): BelongsTo
    {
        return $this->belongsTo(Result::class, 'result_id', 'result_id');
    }
}
