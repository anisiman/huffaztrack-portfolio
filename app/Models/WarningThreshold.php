<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarningThreshold extends Model
{
    protected $fillable = [
        'academic_period_id',
        'warning_1_threshold',
        'warning_2_threshold',
        'warning_3_threshold',
    ];

    /** Long semesters (period 1 & 2): 4 TH / +3 / +3. Short (period 3): 3 TH / +3 / +2. */
    private const LONG_DEFAULTS = [4, 7, 10];
    private const SHORT_DEFAULTS = [3, 6, 8];

    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class, 'academic_period_id');
    }

    /** @return array{0:int,1:int,2:int} */
    public static function defaultsFor(AcademicPeriod $period): array
    {
        return in_array($period->period_number, [1, 2], true) ? self::LONG_DEFAULTS : self::SHORT_DEFAULTS;
    }

    /**
     * The thresholds actually in effect for a period: its saved config if one
     * exists, otherwise the computed default. Never creates a row.
     *
     * @return array{0:int,1:int,2:int}
     */
    public static function effectiveFor(AcademicPeriod $period): array
    {
        $row = $period->relationLoaded('warningThreshold')
            ? $period->warningThreshold
            : static::where('academic_period_id', $period->id)->first();

        return $row
            ? [$row->warning_1_threshold, $row->warning_2_threshold, $row->warning_3_threshold]
            : self::defaultsFor($period);
    }
}
