@extends('layouts.auth')

@section('title', 'HuffazTrack – Reset Password')

@push('styles')
<style>
    .ht-input:focus { border-color: rgba(124,58,237,0.7) !important; box-shadow: 0 0 0 3px rgba(124,58,237,0.15); }
    .signin-btn:hover { opacity: 0.88; transform: translateY(-1px); box-shadow: 0 12px 30px rgba(124,58,237,0.50) !important; }
    .signin-btn { transition: opacity 0.2s, transform 0.2s, box-shadow 0.2s; }
</style>
@endpush

@section('content')

<div style="width:min(440px, 94vw); border-radius:20px; overflow:hidden; border:1px solid rgba(255,255,255,0.10); box-shadow:0 40px 80px rgba(0,0,0,0.45); background:#161140; padding:44px 40px; margin-top:36px; margin-bottom:20px;">

    <div style="display:flex; flex-direction:column; align-items:center; gap:14px; margin-bottom:28px;">
        <div style="width:64px; height:64px; background:linear-gradient(135deg, #7c3aed, #5b21b6); border-radius:18px; display:flex; align-items:center; justify-content:center; box-shadow:0 12px 36px rgba(124,58,237,0.55);">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke="white" stroke-width="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4" stroke="white" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </div>
        <div style="text-align:center;">
            <div style="color:white; font-size:22px; font-weight:800;">Reset Password</div>
            <p style="color:rgba(255,255,255,0.48); font-size:13px; margin:6px 0 0; line-height:1.5;">Choose a new password for your account.</p>
        </div>
    </div>

    {{-- Error alerts (invalid/expired token, validation errors) --}}
    @if($errors->any())
    <div style="background:rgba(220,38,38,0.12); border:1px solid rgba(220,38,38,0.28); border-radius:10px; padding:12px 15px; margin-bottom:18px; color:#f87171; font-size:13px; line-height:1.5;">
        @foreach($errors->all() as $error)
            <div>&bull; {{ $error }}</div>
        @endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" style="display:flex; flex-direction:column; gap:16px;">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label style="display:block; color:rgba(255,255,255,0.55); font-size:10px; letter-spacing:2px; text-transform:uppercase; margin-bottom:8px;">Email</label>
            <input type="email" name="email" value="{{ old('email', $email) }}" placeholder="your@email.com" required autofocus
                class="ht-input"
                style="width:100%; background:rgba(255,255,255,0.07); border:1px solid rgba(255,255,255,0.13); border-radius:10px; padding:12px 14px; color:white; font-size:14px; outline:none; transition:border-color 0.2s, box-shadow 0.2s;">
        </div>

        <div>
            <label style="display:block; color:rgba(255,255,255,0.55); font-size:10px; letter-spacing:2px; text-transform:uppercase; margin-bottom:8px;">New Password</label>
            <input type="password" name="password" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" required minlength="8"
                class="ht-input"
                style="width:100%; background:rgba(255,255,255,0.07); border:1px solid rgba(255,255,255,0.13); border-radius:10px; padding:12px 14px; color:white; font-size:14px; outline:none; transition:border-color 0.2s, box-shadow 0.2s;">
        </div>

        <div>
            <label style="display:block; color:rgba(255,255,255,0.55); font-size:10px; letter-spacing:2px; text-transform:uppercase; margin-bottom:8px;">Confirm New Password</label>
            <input type="password" name="password_confirmation" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" required minlength="8"
                class="ht-input"
                style="width:100%; background:rgba(255,255,255,0.07); border:1px solid rgba(255,255,255,0.13); border-radius:10px; padding:12px 14px; color:white; font-size:14px; outline:none; transition:border-color 0.2s, box-shadow 0.2s;">
        </div>

        <button type="submit" class="signin-btn"
            style="width:100%; background:linear-gradient(135deg,#7c3aed,#6d28d9); border:none; border-radius:10px; padding:13px; color:white; font-size:15px; font-weight:600; cursor:pointer; letter-spacing:0.5px; box-shadow:0 8px 24px rgba(124,58,237,0.40);">
            Reset Password &rarr;
        </button>

        <p style="text-align:center; margin:4px 0 0;">
            <a href="{{ route('login') }}" style="color:#a78bfa; font-size:13px; text-decoration:none;">&larr; Back to Sign In</a>
        </p>
    </form>
</div>

@endsection
