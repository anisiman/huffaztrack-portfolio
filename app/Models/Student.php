<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

use App\Models\Batch;
use App\Models\Sponsorship;
use App\Models\Program;
use App\Models\Lecturer;
use App\Models\User;
use App\Models\Tasmi;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Student extends Model
{
    protected $table = 'student';
    protected $primaryKey = 'student_id';

    public $incrementing = false;
    protected $keyType = 'string';

    // Teammate: status/gender constants
    const STATUS_ACTIVE    = 'AKTIF';
    const STATUS_COMPLETED = 'TAMAT PENSIJILAN';
    const GENDER_MALE    = 'LELAKI';
    const GENDER_FEMALE  = 'PEREMPUAN';

    public $timestamps = true;

    /** Tables (besides `student` itself) whose `student_id` column must be renamed in lockstep — see renameId(). */
    public const ID_REFERENCING_TABLES = [
        'tasmi',
        'student_subject',
        'result',
        'warning_letter',
        'student_programs',
        'attendance',
        'attendance_appeals',
    ];

    /**
     * Safely rename a student_id and propagate it to every table that references it,
     * since student_id is a non-incrementing string primary key with no DB-level
     * ON UPDATE CASCADE. Used by both the `students:normalize-ids` command and the
     * masterlist CSV upload. Returns false (does nothing) if $newId is already taken
     * by a different student, so a rename can never silently overwrite someone else's row.
     */
    public static function renameId(string $oldId, string $newId): bool
    {
        if ($oldId === $newId || $newId === '') {
            return false;
        }

        if (self::where('student_id', $newId)->exists()) {
            return false;
        }

        DB::transaction(function () use ($oldId, $newId) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            DB::table('student')->where('student_id', $oldId)->update(['student_id' => $newId]);

            $childTables = collect(self::ID_REFERENCING_TABLES)
                ->filter(fn ($table) => Schema::hasTable($table) && Schema::hasColumn($table, 'student_id'));

            foreach ($childTables as $table) {
                DB::table($table)->where('student_id', $oldId)->update(['student_id' => $newId]);
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        });

        return true;
    }

    protected $fillable = [
        // IDs
        'student_id',
        'asasi_diploma_id',   // teammate
        'ijazah_id',          // teammate

        // Relationships
        'program_id',
        'sponsorship_id',
        'batch_id',
        'lecturer_id',

        // Personal
        'student_name',
        'gender',
        'nric',
        'email',
        'phone',
        'address',
        'town',               // iman branch
        'state',
        'postcode',           // iman branch
        'secondary_school',
        'race',               // iman branch

        // System
        'status',
        'warning_level',      // iman branch
        'user_id',            // iman branch
        'remarks',            // teammate
    ];

    public function getRouteKeyName(): string
    {
        return 'student_id';
    }

    /** Human-readable warning label */
    public function warningLabel(): string
    {
        return match ((int) ($this->warning_level ?? 0)) {
            1 => 'Warning Letter 1',
            2 => 'Warning Letter 2',
            3 => 'Warning Letter 3',
            default => 'Active',
        };
    }

    /** CSS colour key for badge rendering */
    public function warningColor(): string
    {
        return match ((int) ($this->warning_level ?? 0)) {
            1 => 'amber',
            2 => 'orange',
            3 => 'red',
            default => 'green',
        };
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeOperational(Builder $query): Builder
    {
        return $query->active()->whereNotNull('lecturer_id');
    }

    /**
     * Relationships
     */

    public function sponsorship(): BelongsTo
    {
        return $this->belongsTo(Sponsorship::class, 'sponsorship_id', 'sponsorship_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class, 'batch_id', 'batch_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id', 'program_id');
    }

    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(Program::class, 'student_programs', 'student_id', 'program_id');
    }

    // Teammate: current program via pivot
    public function currentProgram(): BelongsToMany
    {
        return $this->belongsToMany(Program::class, 'student_programs', 'student_id', 'program_id')
            ->withPivot(['is_current', 'start_date', 'end_date'])
            ->wherePivot('is_current', true);
    }

    // Teammate: alias for currentProgram
    public function activeProgram(): BelongsToMany
    {
        return $this->belongsToMany(Program::class, 'student_programs', 'student_id', 'program_id')
            ->withPivot(['is_current', 'start_date', 'end_date'])
            ->wherePivot('is_current', true);
    }

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_id', 'lecturer_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function tasmis(): HasMany
    {
        return $this->hasMany(Tasmi::class, 'student_id', 'student_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'student_id', 'student_id');
    }

    // Teammate: subject and result relationships (models in App\Models namespace)
    public function studentSubjects(): HasMany
    {
        return $this->hasMany(StudentSubject::class, 'student_id', 'student_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class, 'student_id', 'student_id');
    }

    public function khatams(): HasMany
    {
        return $this->hasMany(KhatamRecord::class, 'student_id', 'student_id')->orderByDesc('khatam_date');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(StudentNote::class, 'student_id', 'student_id')->orderByDesc('created_at');
    }

    public function subjectMarks(): HasMany
    {
        return $this->hasMany(SubjectMark::class, 'student_id', 'student_id');
    }

    public function subjectAttendanceRecords(): HasMany
    {
        return $this->hasMany(SubjectAttendanceRecord::class, 'student_id', 'student_id');
    }

    public function studentSubjectSections(): HasMany
    {
        return $this->hasMany(StudentSubjectSection::class, 'student_id', 'student_id');
    }

    /**
     * The subjects this student takes for a given Academic Semester, derived
     * from their Batch's mapped Semester Level (see BatchSemesterLevel) —
     * never manually enrolled.
     *
     * @return \Illuminate\Support\Collection<int, SubjectT>
     */
    public function subjectsForPeriod(?AcademicPeriod $period): \Illuminate\Support\Collection
    {
        if (! $period || ! $this->batch_id) {
            return collect();
        }

        $level = $this->batch?->semesterLevelFor($period);

        if (! $level) {
            return collect();
        }

        return SubjectT::where('semester_id', $level->semester_id)
            ->orderBy('subjectT_code')
            ->get();
    }
}
