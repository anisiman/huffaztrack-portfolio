<?php

namespace App\Services;

use App\Models\AcademicPeriod;
use App\Models\BatchSemesterLevel;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\StudentSubjectSection;
use App\Models\SubjectSection;
use App\Models\SubjectT;
use Illuminate\Support\Collection;

/**
 * Single source of truth for "which students does this lecturer actually
 * teach, for this subject, this Academic Semester" — used by both Carry
 * Marks (SubjectMark) and Subject Attendance. Never queries subject_lecturers;
 * that table has no bearing on ownership.
 *
 * Hafazan: ownership comes from the live Halaqah link (Student::lecturer_id).
 * Madah:   ownership comes from Subject Sections (SubjectSection::lecturer_id)
 *          and who enrolled into them (StudentSubjectSection).
 */
class SubjectOwnershipResolver
{
    /**
     * This lecturer's Halaqah students who are eligible for a Hafazan subject
     * this period (their batch's Semester Level matches the subject's).
     */
    public static function hafazanEligibleStudents(Lecturer $lecturer, SubjectT $subject, AcademicPeriod $period): Collection
    {
        $batchIds = BatchSemesterLevel::where('academic_period_id', $period->id)
            ->where('semester_id', $subject->semester_id)
            ->pluck('batch_id');

        return Student::where('lecturer_id', $lecturer->lecturer_id)
            ->whereIn('batch_id', $batchIds)
            ->active()
            ->with('batch')
            ->orderBy('student_name')
            ->get();
    }

    /**
     * Every Hafazan subject this lecturer currently teaches, this period —
     * i.e. every Hafazan subject for which they have at least one eligible
     * Halaqah student. No section concept applies here.
     *
     * @return Collection<int, array{subject: SubjectT, students: Collection}>
     */
    public static function hafazanSubjectsFor(Lecturer $lecturer, AcademicPeriod $period): Collection
    {
        $myStudents = Student::where('lecturer_id', $lecturer->lecturer_id)->active()->get();

        if ($myStudents->isEmpty()) {
            return collect();
        }

        $semesterIdsByBatch = BatchSemesterLevel::where('academic_period_id', $period->id)
            ->whereIn('batch_id', $myStudents->pluck('batch_id')->unique())
            ->get()
            ->pluck('semester_id', 'batch_id');

        $subjects = SubjectT::where('type', SubjectT::TYPE_HAFAZAN)
            ->whereIn('semester_id', $semesterIdsByBatch->unique()->values())
            ->with('semester')
            ->get();

        return $subjects
            ->map(function (SubjectT $subject) use ($myStudents, $semesterIdsByBatch) {
                $students = $myStudents
                    ->filter(fn (Student $s) => $semesterIdsByBatch->get($s->batch_id) == $subject->semester_id)
                    ->values();

                return $students->isNotEmpty() ? ['subject' => $subject, 'students' => $students] : null;
            })
            ->filter()
            ->values();
    }

    /**
     * Every Section this lecturer is assigned to teach this period, optionally
     * narrowed to one subject. Flat — one row per Section, never grouped by
     * subject — since attendance and enrollment both operate per-Section.
     *
     * @return Collection<int, SubjectSection>
     */
    public static function madahSectionsOwnedBy(Lecturer $lecturer, AcademicPeriod $period, ?SubjectT $subject = null): Collection
    {
        return SubjectSection::where('lecturer_id', $lecturer->lecturer_id)
            ->where('academic_period_id', $period->id)
            ->when($subject, fn ($q) => $q->where('subjectT_id', $subject->subjectT_id))
            ->withCount('enrollments')
            ->with('subject.semester')
            ->get()
            ->sortBy(fn (SubjectSection $s) => $s->subject?->subjectT_code . $s->section_name)
            ->values();
    }

    /** The enrolled, active students in one specific Section. */
    public static function studentsInSection(SubjectSection $section): Collection
    {
        $studentIds = StudentSubjectSection::where('subject_section_id', $section->id)->pluck('student_id');

        return Student::whereIn('student_id', $studentIds)
            ->active()
            ->with('batch')
            ->orderBy('student_name')
            ->get();
    }

    /** Does this lecturer own any part of this subject this period, regardless of type? */
    public static function isAssignedToSubject(Lecturer $lecturer, SubjectT $subject, AcademicPeriod $period): bool
    {
        if ($subject->isMadah()) {
            return static::madahSectionsOwnedBy($lecturer, $period, $subject)->isNotEmpty();
        }

        return static::hafazanEligibleStudents($lecturer, $subject, $period)->isNotEmpty();
    }

    /**
     * This lecturer's combined roster for a subject: Hafazan is simply their
     * Halaqah students; Madah combines every Section of that subject they own
     * (a lecturer may teach more than one Section of the same subject). Used
     * by Carry Marks, where marks are entered per subject, not per Section.
     */
    public static function rosterForSubject(Lecturer $lecturer, SubjectT $subject, AcademicPeriod $period): Collection
    {
        if ($subject->isHafazan()) {
            return static::hafazanEligibleStudents($lecturer, $subject, $period);
        }

        $studentIds = static::madahSectionsOwnedBy($lecturer, $period, $subject)
            ->flatMap(fn (SubjectSection $section) => StudentSubjectSection::where('subject_section_id', $section->id)->pluck('student_id'));

        return Student::whereIn('student_id', $studentIds)
            ->active()
            ->with('batch')
            ->orderBy('student_name')
            ->get();
    }
}
