<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Muqarrar;

class Tasmi extends Model
{
    protected $table = 'tasmi';
    protected $primaryKey = 'tasmi_id';
    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'lecturer_id',
        'muqarrar_id',   // iman branch — batch-aware muqarrar link
        'semester_id',   // teammate
        'week',          // teammate
        'target_page',   // teammate
        'tasmi_date',
    ];

    /** Relationships **/

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_id', 'lecturer_id');
    }

    public function muqarrar(): BelongsTo
    {
        return $this->belongsTo(Muqarrar::class, 'muqarrar_id', 'muqarrar_id');
    }

    // Connects to the detailed progress (the actual page reached)
    public function progress(): HasOne
    {
        return $this->hasOne(TasmiProgress::class, 'tasmi_id', 'tasmi_id');
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'semester_id', 'semester_id');
    }

    public function attendances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Attendance::class, 'tasmi_id', 'tasmi_id');
    }
}
