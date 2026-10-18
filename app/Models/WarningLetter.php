<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarningLetter extends Model
{
    protected $table = 'warning_letter';
    protected $primaryKey = 'warning_id';
    public $timestamps = true;

    protected $fillable = [
        'student_id',
        'attendance_id',
        'attendance_count',
        'warning_level',
        'letter_date',
        'status',
        'remarks',
    ];

    protected $casts = [
        'letter_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class, 'attendance_id', 'attendance_id');
    }

    public function levelLabel(): string
    {
        return match ((int) $this->warning_level) {
            1 => 'Warning Letter 1',
            2 => 'Warning Letter 2',
            3 => 'Warning Letter 3',
            default => 'Active',
        };
    }
}
