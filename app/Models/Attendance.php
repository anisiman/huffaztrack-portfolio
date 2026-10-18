<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $table = 'attendance';
    protected $primaryKey = 'attendance_id';
    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'tasmi_id',
        'session_id',
        'date',
        'status',
        'reason_type',
        'remarks',
        'supporting_document',
        'scanned_at',
    ];

    /** Convenience: unexcused absence only */
    public function isUnexcused(): bool
    {
        return strtolower($this->status ?? '') === 'absent';
    }

    protected $casts = [
        'date' => 'date',
        'scanned_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function tasmi(): BelongsTo
    {
        return $this->belongsTo(Tasmi::class, 'tasmi_id', 'tasmi_id');
    }
}
