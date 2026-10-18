@extends('layouts.auth')

@section('title', 'HuffazTrack – Sign In')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&display=swap" rel="stylesheet">
<style>
    .ht-arabic { font-family: 'Amiri', 'Traditional Arabic', serif; }

    input::placeholder { color: rgba(255,255,255,0.38); }

    .ht-input:focus { border-color: rgba(124,58,237,0.7) !important; box-shadow: 0 0 0 3px rgba(124,58,237,0.15); }

    .signin-btn:hover { opacity: 0.88; transform: translateY(-1px); box-shadow: 0 12px 30px rgba(124,58,237,0.50) !important; }
    .signin-btn { transition: opacity 0.2s, transform 0.2s, box-shadow 0.2s; }

    @media (max-width: 640px) {
        .ht-card { flex-direction: column !important; width: 95vw !important; }
        .ht-left  { flex: none !important; padding: 28px 24px !important; }
        .ht-right { padding: 28px 24px !important; }
    }
</style>
@endpush

@section('content')

{{-- Top marquee bar --}}
<div style="position: fixed; top: 0; left: 0; right: 0; background: rgba(255,255,255,0.03); border-bottom: 1px solid rgba(255,255,255,0.07); padding: 7px 0; z-index: 50; text-align: center;">
    <span style="color: rgba(255,255,255,0.50); font-size: 11px; letter-spacing: 1px; text-transform: uppercase;">
        <span dir="rtl" lang="ar" class="ht-arabic" style="font-size:13px; letter-spacing:0; text-transform:none;">وَرَتِّلِ الْقُرْآنَ تَرْتِيلًا</span>
        &mdash; RECITE THE QURAN WITH MEASURED RECITATION
    </span>
</div>

{{-- Main card --}}
<div class="ht-card" style="display:flex; width: min(900px, 94vw); border-radius: 20px; overflow: hidden; border: 1px solid rgba(255,255,255,0.10); box-shadow: 0 40px 80px rgba(0,0,0,0.45); margin-top: 36px; margin-bottom: 20px;">

    {{-- ── LEFT PANEL ── --}}
    <div class="ht-left" style="flex: 0 0 44%; background: linear-gradient(145deg, #1a1445 0%, #241960 100%); padding: 40px 34px; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative; overflow: hidden;">

        {{-- Ambient glow --}}
        <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:340px; height:340px; background:radial-gradient(circle, rgba(124,58,237,0.32) 0%, transparent 70%); pointer-events:none;"></div>

        {{-- Decorative rings --}}
        <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:220px; height:220px; border-radius:50%; border:1px solid rgba(124,58,237,0.18); pointer-events:none;"></div>
        <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:310px; height:310px; border-radius:50%; border:1px solid rgba(124,58,237,0.09); pointer-events:none;"></div>

        {{-- Logo --}}
        <div style="display:flex; flex-direction:column; align-items:center; gap:18px; position:relative; z-index:1; text-align:center;">
            <div style="width:76px; height:76px; background:linear-gradient(135deg, #7c3aed, #5b21b6); border-radius:20px; display:flex; align-items:center; justify-content:center; flex-shrink:0; position:relative; box-shadow: 0 12px 36px rgba(124,58,237,0.55);">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <div style="position:absolute; bottom:-5px; right:-5px; width:20px; height:20px; background:#f59e0b; border-radius:50%; border:3px solid #241960;"></div>
            </div>
            <div>
                <div style="color:white; font-size:30px; font-weight:800; letter-spacing:0.5px;">HuffazTrack</div>
                <div style="color:rgba(255,255,255,0.45); font-size:10px; letter-spacing:3px; text-transform:uppercase; margin-top:5px;">Student Management</div>
            </div>
            <div dir="rtl" lang="ar" class="ht-arabic" style="margin-top:8px; color:rgba(255,255,255,0.40); font-size:16px; letter-spacing:0; line-height:1.8; padding: 0 10px;">
                وَرَتِّلِ الْقُرْآنَ تَرْتِيلًا
            </div>
        </div>
    </div>

    {{-- ── RIGHT PANEL ── --}}
    <div class="ht-right" style="flex:1; background:#161140; padding:44px 40px; display:flex; flex-direction:column; justify-content:center;">

        {{-- Header --}}
        <div style="margin-bottom:30px;">
            {{-- <div style="display:flex; align-items:center; gap:9px; margin-bottom:14px;">
                <div style="width:26px; height:2px; background:#7c3aed; border-radius:2px;"></div>
                <!--<span style="color:rgba(255,255,255,0.48); font-size:10px; letter-spacing:3px; text-transform:uppercase;">Secure Access</span>-->
            </div> --}}
            <h1 dir="rtl" lang="ar" class="ht-arabic"
                style="background:linear-gradient(135deg,#c4b5fd,#ffffff); -webkit-background-clip:text; background-clip:text; color:transparent; font-size:46px; font-weight:700; line-height:1.3; margin:0 0 6px; letter-spacing:0; text-align:center;">
                أهلاً وسهلاً
            </h1>
            <p style="color:rgba(255,255,255,0.48); font-size:13px; margin:0; line-height:1.5; text-align:center;">Welcome back &mdash; sign in to continue your learning journey.</p>
        </div>

        {{-- Error alerts --}}
        @if($errors->any())
        <div style="background:rgba(220,38,38,0.12); border:1px solid rgba(220,38,38,0.28); border-radius:10px; padding:12px 15px; margin-bottom:18px; color:#f87171; font-size:13px; line-height:1.5;">
            @foreach($errors->all() as $error)
                <div>&bull; {{ $error }}</div>
            @endforeach
        </div>
        @endif

        @if(session('status'))
        <div style="background:rgba(16,185,129,0.12); border:1px solid rgba(16,185,129,0.28); border-radius:10px; padding:12px 15px; margin-bottom:18px; color:#34d399; font-size:13px;">
            {{ session('status') }}
        </div>
        @endif

        {{-- Form --}}
        <form method="POST" action="{{ route('login') }}" style="display:flex; flex-direction:column; gap:16px;">
            @csrf

            {{-- Email --}}
            <div>
                <label style="display:block; color:rgba(255,255,255,0.55); font-size:10px; letter-spacing:2px; text-transform:uppercase; margin-bottom:8px;">Email / Username</label>
                <div style="position:relative;">
                    <div style="position:absolute; left:13px; top:50%; transform:translateY(-50%); color:#8b5cf6; pointer-events:none;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <polyline points="22,6 12,13 2,6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="your@email.com"
                        class="ht-input"
                        style="width:100%; background:rgba(255,255,255,0.07); border:1px solid rgba(255,255,255,0.13); border-radius:10px; padding:12px 14px 12px 40px; color:white; font-size:14px; outline:none; transition:border-color 0.2s, box-shadow 0.2s;">
                </div>
            </div>

            {{-- Password --}}
            <div>
                <label style="display:block; color:rgba(255,255,255,0.55); font-size:10px; letter-spacing:2px; text-transform:uppercase; margin-bottom:8px;">Password</label>
                <div style="position:relative;">
                    <div style="position:absolute; left:13px; top:50%; transform:translateY(-50%); color:#8b5cf6; pointer-events:none;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke="currentColor" stroke-width="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <input type="password" name="password" placeholder="••••••••"
                        class="ht-input"
                        style="width:100%; background:rgba(255,255,255,0.07); border:1px solid rgba(255,255,255,0.13); border-radius:10px; padding:12px 14px 12px 40px; color:white; font-size:14px; outline:none; transition:border-color 0.2s, box-shadow 0.2s;">
                </div>
            </div>

            {{-- Remember + Forgot --}}
            <div style="display:flex; align-items:center; justify-content:space-between;">
                <label style="display:flex; align-items:center; gap:8px; color:rgba(255,255,255,0.50); font-size:13px; cursor:pointer; user-select:none;">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}
                        style="width:15px; height:15px; accent-color:#7c3aed; cursor:pointer;">
                    Remember me
                </label>
                <a href="{{ route('password.request') }}" style="color:#a78bfa; font-size:13px; text-decoration:none; transition:color 0.2s;"
                   onmouseover="this.style.color='#c4b5fd'" onmouseout="this.style.color='#a78bfa'">Forgot password?</a>
            </div>

            {{-- Sign In button --}}
            <button type="submit" class="signin-btn"
                style="width:100%; background:linear-gradient(135deg,#7c3aed,#6d28d9); border:none; border-radius:10px; padding:13px; color:white; font-size:15px; font-weight:600; cursor:pointer; letter-spacing:0.5px; box-shadow:0 8px 24px rgba(124,58,237,0.40); margin-top:2px;">
                Sign In &rarr;
            </button>

            {{-- OR divider --}}
            <div style="display:flex; align-items:center; gap:12px; margin:2px 0;">
                <div style="flex:1; height:1px; background:rgba(255,255,255,0.10);"></div>
                <span style="color:rgba(255,255,255,0.32); font-size:12px; letter-spacing:1.5px;">OR</span>
                <div style="flex:1; height:1px; background:rgba(255,255,255,0.10);"></div>
            </div>

            {{-- Sign Up / role guidance --}}
            <div style="display:flex; flex-direction:column; gap:6px; text-align:center;">
                <p style="color:rgba(255,255,255,0.42); font-size:13px; margin:0; line-height:1.5;">
                    New student?
                    <a href="{{ route('registration') }}" style="color:#a78bfa; text-decoration:none; font-weight:500; transition:color 0.2s;"
                       onmouseover="this.style.color='#c4b5fd'" onmouseout="this.style.color='#a78bfa'">Create your account</a>
                </p>
                <p style="color:rgba(255,255,255,0.28); font-size:12px; margin:0; line-height:1.5;">
                    Lecturer or Admin? Sign in using the credentials provided by your administrator.
                </p>
            </div>
        </form>
    </div>

</div>

@endsection
