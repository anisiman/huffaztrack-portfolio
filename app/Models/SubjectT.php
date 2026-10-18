<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubjectT extends Model
{
    protected $table = 'subjectt';
    protected $primaryKey = 'subjectT_id';
    public $timestamps = false;

    // Hafazan: uses the existing Halaqah lecturer-student assignment, no sections.
    // Madah: academic subjects with lecturer-per-section + student self-enrollment.
    const TYPE_HAFAZAN = 'hafazan';
    const TYPE_MADAH = 'madah';

    protected $fillable = [
        'subjectT_code',
        'subjectT_name',
        'type',
        'credit_hours',
        'has_final_exam',
        'semester_id'
    ];

    protected $casts = [
        'has_final_exam' => 'boolean',
    ];

    public function isHafazan(): bool
    {
        return $this->type === self::TYPE_HAFAZAN;
    }

    public function isMadah(): bool
    {
        return $this->type === self::TYPE_MADAH;
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'semester_id', 'semester_id');
    }

    public function studentSubjects(): HasMany
    {
        return $this->hasMany(StudentSubject::class, 'subjectT_id', 'subjectT_id');
    }

    public function subjectLecturers(): HasMany
    {
        return $this->hasMany(SubjectLecturer::class, 'subjectT_id', 'subjectT_id');
    }

    public function subjectMarks(): HasMany
    {
        return $this->hasMany(SubjectMark::class, 'subjectT_id', 'subjectT_id');
    }

    public function subjectAttendanceSessions(): HasMany
    {
        return $this->hasMany(SubjectAttendanceSession::class, 'subjectT_id', 'subjectT_id');
    }

    public function subjectAttendanceRecords(): HasMany
    {
        return $this->hasMany(SubjectAttendanceRecord::class, 'subjectT_id', 'subjectT_id');
    }

    public function subjectSections(): HasMany
    {
        return $this->hasMany(SubjectSection::class, 'subjectT_id', 'subjectT_id');
    }

    public function studentSubjectSections(): HasMany
    {
        return $this->hasMany(StudentSubjectSection::class, 'subjectT_id', 'subjectT_id');
    }
}
