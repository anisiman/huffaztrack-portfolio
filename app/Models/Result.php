<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    protected $table = 'result'; // Matches ERD bottom table
    protected $primaryKey = 'result_id';
    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'semester_id',
        'gpa',
        'cgpa'
    ];

    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function semester(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Semester::class, 'semester_id', 'semester_id');
    }

    public function subjects(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StudentSubject::class, 'result_id', 'result_id');
    }
}
