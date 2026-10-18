<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminStudentAccountController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $status = $request->query('status', '');

        $query = User::where('role', 'student')->with('student.lecturer')->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('is_active', $status === 'Active');
        }

        $students = $query->paginate(10);

        // For users whose student.user_id link is missing, fall back to email then name matching
        $unlinked = $students->getCollection()->filter(fn($u) => !$u->student);

        $byEmail = $unlinked->isNotEmpty()
            ? Student::with('lecturer')
                ->whereIn('email', $unlinked->pluck('email'))
                ->get()->keyBy('email')
            : collect();

        $byName = $unlinked->isNotEmpty()
            ? Student::with('lecturer')
                ->whereIn('student_name', $unlinked->pluck('name'))
                ->whereNotIn('email', $byEmail->keys())
                ->get()->keyBy('student_name')
            : collect();

        $offset = ($students->currentPage() - 1) * $students->perPage();
        $students->getCollection()->transform(function ($student, $index) use ($offset, $byEmail, $byName) {
            $student->display_no       = $offset + $index + 1;
            $student->display_username = strstr($student->email, '@', true);
            $student->display_status   = $student->is_active ? 'Active' : 'Inactive';
            if (!$student->student) {
                $match = $byEmail->get($student->email) ?? $byName->get($student->name);
                if ($match) {
                    $student->setRelation('student', $match);
                }
            }
            return $student;
        });

        $totalStudents    = User::where('role', 'student')->count();
        $activeStudents   = User::where('role', 'student')->where('is_active', true)->count();
        $inactiveStudents = $totalStudents - $activeStudents;
        $newThisMonth     = User::where('role', 'student')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return view('admin.student-accounts', compact(
            'students', 'search', 'status',
            'totalStudents', 'activeStudents', 'inactiveStudents', 'newThisMonth'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id'      => 'required|string',
            'email'           => 'required|email|unique:users,email',
            'is_active'       => 'required|boolean',
            'password'        => 'required|string|min:8|confirmed',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $studentRecord = Student::where('student_id', $request->student_id)->first();

        if (!$studentRecord) {
            return back()->withInput()
                ->withErrors(['student_id' => 'Student ID not found in the masterlist.']);
        }

        if ($studentRecord->user_id) {
            return back()->withInput()
                ->withErrors(['student_id' => 'This student already has an account. Edit it instead.']);
        }

        $profilePicture = $request->hasFile('profile_picture')
            ? $request->file('profile_picture')->store('avatars', 'public')
            : null;

        $user = User::create([
            'name'            => $studentRecord->student_name,
            'email'           => $request->email,
            'is_active'       => $request->is_active,
            'password'        => Hash::make($request->password),
            'role'            => 'student',
            'profile_picture' => $profilePicture,
        ]);

        $studentRecord->update(['user_id' => $user->id]);

        return redirect()->route('admin.students')->with('success', "Account for {$studentRecord->student_name} created successfully.");
    }

    public function update(Request $request, User $student)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email,' . $student->id,
            'is_active'       => 'required|boolean',
            'password'        => 'nullable|string|min:8|confirmed',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'remove_avatar'   => 'nullable|boolean',
        ]);

        $data = [
            'name'      => $request->name,
            'email'     => $request->email,
            'is_active' => $request->is_active,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('profile_picture')) {
            if ($student->profile_picture) {
                Storage::disk('public')->delete($student->profile_picture);
            }
            $data['profile_picture'] = $request->file('profile_picture')->store('avatars', 'public');
        } elseif ($request->boolean('remove_avatar')) {
            if ($student->profile_picture) {
                Storage::disk('public')->delete($student->profile_picture);
            }
            $data['profile_picture'] = null;
        }

        $student->update($data);

        return redirect()->route('admin.students')->with('success', 'Student account updated successfully.');
    }

    public function destroy(User $student)
    {
        if ($student->profile_picture) {
            Storage::disk('public')->delete($student->profile_picture);
        }

        $student->delete();

        return redirect()->route('admin.students')->with('success', 'Student account deleted successfully.');
    }

    public function resetPassword(Request $request, User $student)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $student->update(['password' => Hash::make($request->password)]);

        return redirect()->route('admin.students')->with('success', "Password for {$student->name} has been reset successfully.");
    }
}
