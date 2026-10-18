<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function show()
    {
        return view('registration');
    }

    public function store(Request $request)
    {
        // Step 1: Basic field validation
        $request->validate([
            'student_id' => 'required|string',
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|min:6|confirmed',
        ]);

        // Step 2: Check that the Student ID exists in the masterlist
        $student = Student::where('student_id', $request->student_id)->first();

        if (!$student) {
            return back()
                ->withInput()
                ->withErrors(['student_id' => 'Student ID not found. Please check your ID or contact your administrator.']);
        }

        // Step 3: Self-registration needs an email on the masterlist record to
        // compare against; without one, the administrator creates the account.
        if (! $student->email) {
            return back()
                ->withInput()
                ->withErrors(['student_id' => 'No email address is on record for this Student ID. Please contact your administrator to create your account.']);
        }

        // Step 4: Check that the email matches the student's registered email
        if (strtolower($student->email) !== strtolower($request->email)) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'Email does not match the records for this Student ID.']);
        }

        // Step 5: Check that this student does not already have an account
        if ($student->user_id) {
            return back()
                ->withInput()
                ->withErrors(['student_id' => 'An account for this Student ID already exists. Please sign in instead.']);
        }

        // Step 6: Create the user account (role is always student for public registration)
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'student',
        ]);

        // Step 7: Link the new user account to the student record
        $student->update(['user_id' => $user->id]);

        NotificationService::notifyAdmins('account', 'New Student Registration', "{$user->name} has registered a student account.", '/admin/students');

        return redirect()->route('login')
                         ->with('success', 'Account created successfully! You can now sign in.');
    }
}
