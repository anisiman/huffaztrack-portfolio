<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lecturer extends Model
{
    protected $table = 'lecturer';
    protected $primaryKey = 'lecturer_id';
    public $timestamps = false;

    protected $fillable = [
        'lecturer_name',
        'phone',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'lecturer_id', 'lecturer_id');
    }

    public function tasmi()
    {
        return $this->hasMany(Tasmi::class, 'lecturer_id');
    }

    public function subjectLecturers(): HasMany
    {
        return $this->hasMany(SubjectLecturer::class, 'lecturer_id', 'lecturer_id');
    }

    public function subjectMarks(): HasMany
    {
        return $this->hasMany(SubjectMark::class, 'lecturer_id', 'lecturer_id');
    }

    public function subjectAttendanceSessions(): HasMany
    {
        return $this->hasMany(SubjectAttendanceSession::class, 'lecturer_id', 'lecturer_id');
    }

    public function subjectAttendanceRecords(): HasMany
    {
        return $this->hasMany(SubjectAttendanceRecord::class, 'lecturer_id', 'lecturer_id');
    }

    public function subjectSections(): HasMany
    {
        return $this->hasMany(SubjectSection::class, 'lecturer_id', 'lecturer_id');
    }
}