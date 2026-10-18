<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceAppeal extends Model
{
    protected $table = 'attendance_appeals';

    protected $fillable = [
        'attendance_id',
        'student_id',
        'lecturer_id',
        'date',
        'reason_type',
        'remarks',
        'document_path',
        'status',
        'rejection_remarks',
        'decided_at',
    ];

    protected $casts = [
        'date'        => 'date',
        'decided_at'  => 'datetime',
    ];

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class, 'attendance_id', 'attendance_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_id', 'lecturer_id');
    }

    public function isPending(): bool  { return $this->status === 'pending'; }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }
}
