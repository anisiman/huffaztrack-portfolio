<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\AttendanceAppeal;
use App\Models\HalaqahAssignment;
use App\Models\Lecturer;
use App\Models\Student;      // teammate — needed for assignment & destroy
use App\Models\User;         // iman branch — needed for auth management
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;   // iman branch — password hashing
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;        // iman branch — unique-ignore validation

class AdminLecturerController extends Controller
{
    public function index(Request $request)
    {
        [$periods, $currentPeriod, $selectedPeriodId, $viewingPeriod, $isHistorical] = $this->resolvePeriod($request);

        $lecturerProfiles = $this->withRosters(
            Lecturer::with('user')->orderBy('lecturer_name')->get(),
            $viewingPeriod
        );

        $unassignedCount = Student::where('status', 'AKTIF')->whereNull('lecturer_id')->count();

        return view('admin.lecturers', compact(
            'lecturerProfiles', 'unassignedCount',
            'periods', 'currentPeriod', 'selectedPeriodId', 'viewingPeriod', 'isHistorical'
        ));
    }

    /**
     * One halaqah per page: the lecturer, their students, and the assign panel.
     */
    public function show(Request $request, $lecturer_id)
    {
        [$periods, $currentPeriod, $selectedPeriodId, $viewingPeriod, $isHistorical] = $this->resolvePeriod($request);

        $lecturer = $this->withRosters(
            Lecturer::with('user')->whereKey($lecturer_id)->get(),
            $viewingPeriod
        )->first();

        abort_unless($lecturer, 404);

        $unassignedStudents = $isHistorical
            ? collect()
            : Student::where('status', 'AKTIF')->whereNull('lecturer_id')->orderBy('student_name')->get();

        // Transfer targets: every other lecturer, with their current load.
        $otherLecturers = $isHistorical
            ? collect()
            : Lecturer::with('user')->withCount('students')
                ->whereKeyNot($lecturer->lecturer_id)
                ->orderBy('lecturer_name')
                ->get();

        return view('admin.lecturer-halaqah', compact(
            'lecturer', 'unassignedStudents', 'otherLecturers',
            'periods', 'currentPeriod', 'selectedPeriodId', 'viewingPeriod', 'isHistorical'
        ));
    }

    private function resolvePeriod(Request $request): array
    {
        $periods = AcademicPeriod::orderByDesc('start_date')->get();
        $currentPeriod = AcademicPeriod::current();

        $selectedPeriodId = $request->filled('period') ? (int) $request->query('period') : null;
        $viewingPeriod = $selectedPeriodId ? $periods->firstWhere('id', $selectedPeriodId) : null;

        return [$periods, $currentPeriod, $selectedPeriodId, $viewingPeriod, (bool) $viewingPeriod];
    }

    /**
     * Attach each lecturer's roster as the `students` relation — live from
     * student.lecturer_id, or rebuilt from a past period's HalaqahAssignment rows.
     */
    private function withRosters($lecturers, ?AcademicPeriod $viewingPeriod)
    {
        if (! $viewingPeriod) {
            return $lecturers->load(['students' => fn ($q) => $q->orderBy('student_name')]);
        }

        // Read-only snapshot: rebuild each lecturer's roster from that period's
        // HalaqahAssignment rows rather than the live lecturer_id link, so past
        // semesters keep showing who was actually assigned back then.
        $assignments = HalaqahAssignment::where('academic_period_id', $viewingPeriod->id)
            ->whereIn('lecturer_id', $lecturers->pluck('lecturer_id'))
            ->get();
        $assignmentsByLecturer = $assignments->groupBy('lecturer_id');
        $liveStudentsById = Student::whereIn('student_id', $assignments->pluck('student_id')->filter()->unique())
            ->get()
            ->keyBy('student_id');

        return $lecturers->map(function (Lecturer $lecturer) use ($assignmentsByLecturer, $liveStudentsById) {
            $rows = $assignmentsByLecturer->get($lecturer->lecturer_id, collect())
                ->sortBy('student_name_snapshot')
                ->map(function (HalaqahAssignment $assignment) use ($liveStudentsById) {
                    $liveStudent = $assignment->student_id ? $liveStudentsById->get($assignment->student_id) : null;

                    return (object) [
                        'student_id' => $assignment->student_id ?? '—',
                        'student_name' => $liveStudent->student_name ?? $assignment->student_name_snapshot,
                        'status' => $liveStudent->status ?? '—',
                    ];
                })
                ->values();

            $lecturer->setRelation('students', $rows);
            return $lecturer;
        });
    }

    public function accountsIndex(Request $request)
    {
        $search = $request->query('search', '');
        $status = $request->query('status', '');

        $query = User::where('role', 'lecturer')
            ->with(['lecturerProfile' => fn ($q) => $q->withCount('students')])
            ->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('lecturerProfile', fn ($lq) =>
                      $lq->where('lecturer_name', 'like', "%{$search}%")
                  );
            });
        }

        if ($status) {
            $query->where('is_active', $status === 'Active');
        }

        $lecturers = $query->paginate(10);
        $offset    = ($lecturers->currentPage() - 1) * $lecturers->perPage();

        $lecturers->getCollection()->transform(function ($lecturer, $index) use ($offset) {
            $lecturer->display_no          = $offset + $index + 1;
            $lecturer->display_lecturer_id = $lecturer->lecturerProfile?->lecturer_id ?? '—';
            $lecturer->display_username    = strstr($lecturer->email, '@', true);
            $lecturer->display_status      = $lecturer->is_active ? 'Active' : 'Inactive';
            return $lecturer;
        });

        $lecturerProfiles = Lecturer::orderBy('lecturer_name')->get();

        $totalLecturers    = User::where('role', 'lecturer')->count();
        $activeLecturers   = User::where('role', 'lecturer')->where('is_active', true)->count();
        $inactiveLecturers = $totalLecturers - $activeLecturers;
        $permanentLecturers = $activeLecturers;
        $partTimeLecturers  = $inactiveLecturers;

        return view('admin.lecturer-accounts', compact(
            'lecturers', 'search', 'status', 'lecturerProfiles',
            'totalLecturers', 'permanentLecturers', 'partTimeLecturers'
        ));
    }

    // iman branch: redirect stubs (form lives in the index modal)
    public function create()
    {
        return redirect()->route('admin.lecturers');
    }

    public function edit(User $lecturer)
    {
        return redirect()->route('admin.lecturers');
    }

    /**
     * iman branch: create a User account for an existing Lecturer profile.
     * Links the User account to the Lecturer record via user_id.
     */
    public function store(Request $request)
    {
        $request->validate([
            'lecturer_profile_id' => [
                'required',
                'exists:lecturer,lecturer_id',
                function ($attr, $value, $fail) {
                    $profile = Lecturer::find($value);
                    if ($profile && $profile->user_id !== null) {
                        $fail('This lecturer profile is already assigned to an account.');
                    }
                },
            ],
            'email'           => 'required|email|unique:users,email',
            'is_active'       => 'required|boolean',
            'password'        => 'required|string|min:8|confirmed',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $profile = Lecturer::findOrFail($request->lecturer_profile_id);

        $profilePicture = $request->hasFile('profile_picture')
            ? $request->file('profile_picture')->store('avatars', 'public')
            : null;

        $user = User::create([
            'name'            => $profile->lecturer_name,
            'email'           => $request->email,
            'is_active'       => $request->is_active,
            'password'        => Hash::make($request->password),
            'role'            => 'lecturer',
            'profile_picture' => $profilePicture,
        ]);

        $profile->update(['user_id' => $user->id]);

        NotificationService::notifyAdmins('account', 'Lecturer Account Created', "Account for {$profile->lecturer_name} has been created.", '/admin/lecturer-accounts');

        return redirect()->route('admin.lecturerAccounts')
            ->with('success', "Account for {$profile->lecturer_name} created successfully.");
    }

    /**
     * iman branch: update the User account and re-link to a (possibly different) Lecturer profile.
     */
    public function update(Request $request, User $lecturer)
    {
        $request->validate([
            'lecturer_profile_id' => [
                'required',
                'exists:lecturer,lecturer_id',
                function ($attr, $value, $fail) use ($lecturer) {
                    $profile = Lecturer::find($value);
                    if ($profile && $profile->user_id !== null && $profile->user_id != $lecturer->id) {
                        $fail('This lecturer profile is already assigned to another account.');
                    }
                },
            ],
            'email'           => ['required', 'email', Rule::unique('users', 'email')->ignore($lecturer->id)],
            'is_active'       => 'required|boolean',
            'password'        => 'nullable|string|min:8|confirmed',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'remove_avatar'   => 'nullable|boolean',
        ]);

        $profile = Lecturer::findOrFail($request->lecturer_profile_id);
        $wasActive = $lecturer->is_active;

        // Unlink old profile if it changed
        $oldProfile = Lecturer::where('user_id', $lecturer->id)->first();
        if ($oldProfile && $oldProfile->lecturer_id != $profile->lecturer_id) {
            $oldProfile->update(['user_id' => null]);
        }

        $data = [
            'name'      => $profile->lecturer_name,
            'email'     => $request->email,
            'is_active' => $request->is_active,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('profile_picture')) {
            if ($lecturer->profile_picture) {
                Storage::disk('public')->delete($lecturer->profile_picture);
            }
            $data['profile_picture'] = $request->file('profile_picture')->store('avatars', 'public');
        } elseif ($request->boolean('remove_avatar')) {
            if ($lecturer->profile_picture) {
                Storage::disk('public')->delete($lecturer->profile_picture);
            }
            $data['profile_picture'] = null;
        }

        $lecturer->update($data);
        $profile->update(['user_id' => $lecturer->id]);

        $message = 'Lecturer account updated successfully.';

        if ($wasActive && ! $request->boolean('is_active')) {
            $releasedCount = Student::where('lecturer_id', $profile->lecturer_id)->count();
            Student::where('lecturer_id', $profile->lecturer_id)->update(['lecturer_id' => null]);

            if ($releasedCount > 0) {
                $message .= " Lecturer deactivated — {$releasedCount} " . ($releasedCount === 1 ? 'student' : 'students') . ' released from the halaqah.';
            }
        }

        return redirect()->back()
            ->with('success', $message);
    }

    /**
     * Combined: releases all assigned students (teammate) then deletes the Lecturer profile.
     * Route uses {lecturer_id} so we find by Lecturer PK, not User model binding.
     */
    public function destroy(Request $request, $lecturer_id)
    {
        $lecturer = Lecturer::findOrFail($lecturer_id);

        // Teammate: release all students assigned to this lecturer
        Student::where('lecturer_id', $lecturer_id)->update(['lecturer_id' => null]);

        // Unlink any User account from this profile (keeps the User account, just removes the link)
        $lecturer->update(['user_id' => null]);

        $lecturer->delete();

        // Deleting from the lecturer's own halaqah page: going back would 404.
        $redirect = $request->input('return') === 'halaqah'
            ? redirect()->route('admin.lecturers')
            : redirect()->back();

        return $redirect
            ->with('success', 'Lecturer deleted. Assigned students have been released.');
    }

    /**
     * Delete a lecturer User account that has no linked Lecturer profile.
     */
    public function destroyAccount(User $user)
    {
        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        $user->delete();

        return redirect()->back()
            ->with('success', 'Lecturer account deleted successfully.');
    }

    /**
     * Teammate: create a Lecturer profile record (name only, no User account).
     */
    public function storeProfile(Request $request)
    {
        $request->validate([
            'lecturer_name' => 'required|string|max:255',
        ]);

        Lecturer::create([
            'lecturer_name' => $request->lecturer_name,
        ]);

        return redirect()->route('admin.lecturers')
            ->with('success', "Lecturer \"{$request->lecturer_name}\" profile created successfully.");
    }

    /**
     * iman branch: reset password for a lecturer's User account.
     */
    public function resetPassword(Request $request, User $lecturer)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $lecturer->update(['password' => Hash::make($request->password)]);

        $name = $lecturer->lecturerProfile?->lecturer_name ?? $lecturer->name;

        return redirect()->route('admin.lecturerAccounts')
            ->with('success', "Password for {$name} has been reset successfully.");
    }

    /**
     * Teammate: assign a student to a lecturer.
     */
    public function assignStudent(Request $request)
    {
        $request->validate([
            'lecturer_id'   => 'required|exists:lecturer,lecturer_id',
            'student_ids'   => 'required|array|min:1',
            'student_ids.*' => 'string|exists:student,student_id',
        ]);

        // Only take students that are still unassigned, so a stale page can't
        // silently pull a student out of another lecturer's halaqah.
        $assigned = Student::whereIn('student_id', $request->student_ids)
            ->whereNull('lecturer_id')
            ->update(['lecturer_id' => $request->lecturer_id]);

        $skipped = count(array_unique($request->student_ids)) - $assigned;

        $message = $assigned . ' ' . ($assigned === 1 ? 'student' : 'students') . ' assigned successfully.';
        if ($skipped > 0) {
            $message .= " {$skipped} skipped because they were already assigned to another lecturer.";
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Move a halaqah (all of it, or the selected students) to another lecturer.
     * Past tasmi/attendance rows keep the lecturer who recorded them; the new
     * lecturer sees them because lecturer pages scope records by student.
     */
    public function transferHalaqah(Request $request, $lecturer_id)
    {
        $from = Lecturer::findOrFail($lecturer_id);

        $validated = $request->validate([
            'to_lecturer_id' => ['required', 'exists:lecturer,lecturer_id', Rule::notIn([$from->lecturer_id])],
            'student_ids'    => 'required|array|min:1',
            'student_ids.*'  => 'string',
        ], [
            'to_lecturer_id.not_in' => 'Choose a different lecturer to transfer to.',
            'student_ids.required'  => 'Select at least one student to transfer.',
        ]);

        $to = Lecturer::findOrFail($validated['to_lecturer_id']);

        $moved = DB::transaction(function () use ($from, $to, $validated) {
            // Only students still in this halaqah — ignores anything stale in the form.
            $studentIds = Student::where('lecturer_id', $from->lecturer_id)
                ->whereIn('student_id', $validated['student_ids'])
                ->pluck('student_id');

            Student::whereIn('student_id', $studentIds)->update(['lecturer_id' => $to->lecturer_id]);

            // Pending excuse letters follow the student so the new lecturer can decide them.
            AttendanceAppeal::whereIn('student_id', $studentIds)
                ->where('lecturer_id', $from->lecturer_id)
                ->where('status', 'pending')
                ->update(['lecturer_id' => $to->lecturer_id]);

            return $studentIds->count();
        });

        return redirect()->route('admin.lecturers.show', $to->lecturer_id)
            ->with('success', "{$moved} " . ($moved === 1 ? 'student' : 'students') . " transferred from {$from->lecturer_name} to {$to->lecturer_name}. Their past hafazan and attendance records remain visible to the new lecturer.");
    }

    /**
     * Teammate: remove a student from their assigned lecturer.
     */
    public function removeStudent($student_id)
    {
        $student = Student::findOrFail($student_id);
        $student->lecturer_id = null;
        $student->save();

        return redirect()->back()
            ->with('success', "Student removed from lecturer's list successfully.");
    }
}
