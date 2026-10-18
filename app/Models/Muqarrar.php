<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Muqarrar extends Model
{
    protected $table = 'muqarrar';
    protected $primaryKey = 'muqarrar_id';
    public $timestamps = false;

    protected $fillable = [
        'semester_id', 'batch_id', 'academic_period_id', 'week', 'week_start_date', 'start_page', 'end_page', 'notes',
    ];

    /**
     * Legacy student-level (1-7) this record was created under. New records
     * leave this null and use academic_period_id instead.
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'semester_id', 'semester_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class, 'batch_id', 'batch_id');
    }

    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class, 'academic_period_id');
    }
}