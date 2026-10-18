<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Intentionally ignore the broker's status code here so the response
        // is identical whether or not the email is registered. Branching on
        // it (e.g. showing "email not found") would let an attacker enumerate
        // valid accounts.
        Password::sendResetLink($request->only('email'));

        return back()->with(
            'status',
            'If an account exists for that email address, a password reset link has been sent.'
        );
    }
}
