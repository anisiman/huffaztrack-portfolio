<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class College extends Model
{
    protected $table = 'college';

    protected $primaryKey = 'college_id';

    public $timestamps = false;

    protected $fillable = [
        'college_name',
        'campus'
    ];

    public function programs(): HasMany
    {
        return $this->hasMany(Program::class, 'college_id', 'college_id');
    }
}