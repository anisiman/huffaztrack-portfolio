<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AcademicPeriod extends Model
{
    protected $fillable = [
        'name',
        'academic_year',
        'period_number',
        'start_date',
        'end_date',
        'status',
        'status_locked',
        'section_enrollment_open',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'status_locked' => 'boolean',
        'section_enrollment_open' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function halaqahAssignments(): HasMany
    {
        return $this->hasMany(HalaqahAssignment::class, 'academic_period_id');
    }

    public function warningThreshold(): HasOne
    {
        return $this->hasOne(WarningThreshold::class, 'academic_period_id');
    }

    public function batchSemesterLevels(): HasMany
    {
        return $this->hasMany(BatchSemesterLevel::class, 'academic_period_id');
    }

    public function subjectLecturers(): HasMany
    {
        return $this->hasMany(SubjectLecturer::class, 'academic_period_id');
    }

    public function subjectMarks(): HasMany
    {
        return $this->hasMany(SubjectMark::class, 'academic_period_id');
    }

    public function subjectAttendanceSessions(): HasMany
    {
        return $this->hasMany(SubjectAttendanceSession::class, 'academic_period_id');
    }

    public function subjectAttendanceRecords(): HasMany
    {
        return $this->hasMany(SubjectAttendanceRecord::class, 'academic_period_id');
    }

    public function subjectSections(): HasMany
    {
        return $this->hasMany(SubjectSection::class, 'academic_period_id');
    }

    public function studentSubjectSections(): HasMany
    {
        return $this->hasMany(StudentSubjectSection::class, 'academic_period_id');
    }

    /** The currently active period, if any. */
    public static function current(): ?self
    {
        return static::active()->orderByDesc('start_date')->first();
    }

    /** The period immediately preceding this one by start date, if any. */
    public function previous(): ?self
    {
        return static::where('start_date', '<', $this->start_date)
            ->orderByDesc('start_date')
            ->first();
    }

    /** Upcoming/active/completed purely from today vs. this period's dates. */
    public function computeStatus(?Carbon $today = null): string
    {
        $today = $today ?? Carbon::today();
        $start = Carbon::parse($this->start_date)->startOfDay();
        $end = Carbon::parse($this->end_date)->endOfDay();

        if ($today->lt($start)) {
            return 'upcoming';
        }

        if ($today->gt($end)) {
            return 'completed';
        }

        return 'active';
    }

    /**
     * Recompute status for every non-locked period from today's date, then
     * enforce that at most one period is Active — preferring a manually
     * locked Active period over auto-computed ones, and the most recently
     * started one if more than one auto-computed row ends up Active (e.g.
     * from overlapping date ranges).
     */
    public static function recalculateAll(): void
    {
        $today = Carbon::today();

        foreach (static::where('status_locked', false)->get() as $period) {
            $computed = $period->computeStatus($today);
            if ($period->status !== $computed) {
                $period->update(['status' => $computed]);
            }
        }

        $activeOnes = static::active()->get();
        if ($activeOnes->count() > 1) {
            $keep = $activeOnes->firstWhere('status_locked', true)
                ?? $activeOnes->sortByDesc('start_date')->first();

            static::active()
                ->where('id', '!=', $keep->id)
                ->update(['status' => 'completed']);
        }
    }

    /** Manually promote this period to Active, demoting any other Active period. */
    public function activate(): void
    {
        static::active()->where('id', '!=', $this->id)->update(['status' => 'completed']);

        $this->update([
            'status' => 'active',
            'status_locked' => true,
        ]);
    }
}
