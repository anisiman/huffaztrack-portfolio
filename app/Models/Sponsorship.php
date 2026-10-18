<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sponsorship extends Model
{
    protected $table = 'sponsorship';
    protected $primaryKey = 'sponsorship_id';

    protected $fillable = [
        'sponsorship_name',
        'sponsorship_duration'
    ];

    public $timestamps = false;
}