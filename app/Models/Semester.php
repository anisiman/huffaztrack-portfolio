<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    protected $table = 'semester';
    protected $primaryKey = 'semester_id';
    public $timestamps = false;

    protected $fillable = [
        'semester_name'
    ];
}