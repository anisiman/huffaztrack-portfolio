<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        if ($user->role === 'student') {
            $user->load([
                'student.batch',
                'student.program',
                'student.sponsorship',
                'student.lecturer',
            ]);
        }

        return view('profile.show', compact('user'));
    }

    public function biodata()
    {
        $user = Auth::user();

        if ($user->role !== 'student' || !$user->student) {
            abort(403);
        }

        $student = $user->student->load([
            'batch',
            'program',
            'sponsorship',
            'lecturer',
            'results.semester',
            'results.subjects.subjectT',
        ]);

        return view('student.biodata', compact('student'));
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $user = Auth::user();

        // Delete old picture if it exists
        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        $path = $request->file('profile_picture')->store('avatars', 'public');

        $user->update(['profile_picture' => $path]);

        return back()->with('avatar_success', 'Profile picture updated successfully!');
    }

    public function removeAvatar()
    {
        $user = Auth::user();

        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
            $user->update(['profile_picture' => null]);
        }

        return back()->with('avatar_success', 'Profile picture removed.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return back()
                ->withErrors(['current_password' => 'Current password is incorrect.'])
                ->withInput();
        }

        Auth::user()->update([
            'password' => Hash::make($request->new_password),
        ]);

        return back()->with('success', 'Password updated successfully!');
    }
}
