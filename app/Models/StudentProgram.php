<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentProgram extends Model
{
    protected $table = 'student_programs';
    public $timestamps = true;
    
    protected $fillable = [
        'student_id',
        'program_id',
        'start_date',
        'end_date',
        'is_current'
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id', 'program_id');
    }
}