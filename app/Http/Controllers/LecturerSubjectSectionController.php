<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\Lecturer;
use App\Models\SubjectSection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LecturerSubjectSectionController extends Controller
{
    /** Read-only "My Sections": sections this lecturer is assigned to teach this period. */
    public function index(): View
    {
        $lecturer = Lecturer::where('user_id', Auth::id())->first();
        $period = AcademicPeriod::current();

        $sections = collect();

        if ($lecturer && $period) {
            $sections = SubjectSection::where('lecturer_id', $lecturer->lecturer_id)
                ->where('academic_period_id', $period->id)
                ->with(['subject.semester', 'enrollments.student'])
                ->get()
                ->sortBy(fn ($section) => $section->subject?->subjectT_code)
                ->values();
        }

        return view('lecturer.sections.index', compact('lecturer', 'period', 'sections'));
    }
}
