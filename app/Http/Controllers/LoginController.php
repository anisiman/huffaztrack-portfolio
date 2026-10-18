<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    //
    public function showLoginForm() {
        return view('login'); // blade login page
    }

    public function login(Request $request) {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            if ($user->role === 'admin') {
                return redirect()->intended(route('admin.dashboard'));
            } elseif ($user->role === 'lecturer') {
                return redirect()->intended(route('lecturer.dashboard'));
            } else {
                return redirect()->intended(route('student.dashboard'));
            }
        }

        return back()->withErrors([
            'email' => 'Email or password is incorrect.',
        ])->onlyInput('email');
    }

    public function logout() {
        Auth::logout();
        return redirect('/login');
    }
}
