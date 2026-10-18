<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Program extends Model
{
    protected $table = 'program';
    protected $primaryKey = 'program_id';
    public $timestamps = false;
    protected $fillable = ['college_id', 'program_name', 'program_level'];

    public function college(): BelongsTo {
        return $this->belongsTo(College::class, 'college_id', 'college_id');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_programs', 'program_id', 'student_id');
    }
}