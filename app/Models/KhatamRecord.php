<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class KhatamRecord extends Model
{
    protected $fillable = [
        'student_id',
        'khatam_date',
        'notes',
    ];

    protected $casts = [
        'khatam_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    /**
     * Khatam count per student, counting only khatams dated on or before $asOf.
     * Students with none are absent from the result.
     *
     * A student with at least one khatam has already fulfilled the Muqarrar —
     * they may be revising from page 1 again — so every Hafazan status calculation
     * treats a recorded page as "Completed" instead of comparing it against the
     * batch checkpoint. Using khatam_date keeps historical reports honest: before
     * their first khatam the student is still judged normally.
     *
     * @return Collection<string, int>  student_id => count
     */
    public static function countsAsOf(iterable $studentIds, $asOf): Collection
    {
        $ids = collect($studentIds)->values();
        if ($ids->isEmpty()) {
            return collect();
        }

        return static::query()
            ->whereIn('student_id', $ids->all())
            ->whereDate('khatam_date', '<=', Carbon::parse($asOf)->toDateString())
            ->selectRaw('student_id, COUNT(*) as khatam_count')
            ->groupBy('student_id')
            ->toBase()
            ->pluck('khatam_count', 'student_id')
            ->map(fn ($n) => (int) $n);
    }
}
