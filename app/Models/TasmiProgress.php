<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TasmiProgress extends Model
{
    protected $table = 'tasmi_progress';
    protected $primaryKey = 'progress_id';
    public $timestamps = false;

    protected $fillable = [
        'tasmi_id', 'page_reached'
    ];

    public function tasmi(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tasmi::class, 'tasmi_id', 'tasmi_id');
    }
}
