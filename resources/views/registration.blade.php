@extends('layouts.auth')

@section('title', 'HuffazTrack – Create Account')

@push('styles')
<style>
    input::placeholder { color: rgba(255,255,255,0.38); }
    select option { background: #1e1845; color: white; }

    .ht-input:focus { border-color: rgba(124,58,237,0.7) !important; box-shadow: 0 0 0 3px rgba(124,58,237,0.15); }

    .signup-btn:hover { opacity: 0.88; transform: translateY(-1px); box-shadow: 0 12px 30px rgba(124,58,237,0.50) !important; }
    .signup-btn { transition: opacity 0.2s, transform 0.2s, box-shadow 0.2s; }

    @media (max-width: 640px) {
        .ht-card { flex-direction: column !important; width: 95vw !important; }
        .ht-left  { flex: none !important; padding: 28px 24px !important; min-height: 200px; }
        .ht-right { padding: 28px 24px !important; }
    }
</style>
@endpush

@section('content')

{{-- Top marquee bar --}}
<div style="position: fixed; top: 0; left: 0; right: 0; background: rgba(255,255,255,0.03); border-bottom: 1px solid rgba(255,255,255,0.07); padding: 7px 0; z-index: 50; text-align: center;">
    <span style="color: rgba(255,255,255,0.50); font-size: 11px; letter-spacing: 2.5px; text-transform: uppercase;">
        وَرَتِّلِ الْقُرْآنَ تَرْتِيلًا &mdash; RECITE THE QURAN WITH MEASURED RECITATION
    </span>
</div>

{{-- Main card --}}
<div class="ht-card" style="display:flex; width: min(900px, 94vw); border-radius: 20px; overflow: hidden; border: 1px solid rgba(255,255,255,0.10); box-shadow: 0 40px 80px rgba(0,0,0,0.45); margin-top: 36px; margin-bottom: 20px;">

    {{-- ── LEFT PANEL ── --}}
    <div class="ht-left" style="flex: 0 0 40%; background: linear-gradient(145deg, #1a1445 0%, #241960 100%); padding: 40px 34px; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative; overflow: hidden;">

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
            <div style="margin-top:8px; color:rgba(255,255,255,0.30); font-size:13px; letter-spacing:1px; line-height:1.8; padding: 0 10px;">
                وَرَتِّلِ الْقُرْآنَ تَرْتِيلًا
            </div>
        </div>
    </div>

    {{-- ── RIGHT PANEL ── --}}
    <div class="ht-right" style="flex:1; background:#161140; padding:40px 40px; display:flex; flex-direction:column; justify-content:center; overflow-y:auto;">

        {{-- Header --}}
        <div style="margin-bottom:24px;">
            <div style="display:flex; align-items:center; gap:9px; margin-bottom:14px;">
                <div style="width:26px; height:2px; background:#7c3aed; border-radius:2px;"></div>
                <span style="color:rgba(255,255,255,0.48); font-size:10px; letter-spacing:3px; text-transform:uppercase;">Student Registration</span>
            </div>
            <h1 style="color:white; font-size:34px; font-weight:800; line-height:1.05; margin:0 0 10px; letter-spacing:-0.5px;">Student<br>Registration</h1>
            <p style="color:rgba(255,255,255,0.48); font-size:13px; margin:0; line-height:1.5;">For students only — your Student ID must already be in the system to register.</p>
            <p style="color:rgba(255,255,255,0.28); font-size:12px; margin:8px 0 0; line-height:1.6;">Lecturer or Admin? Your account is created by the system administrator. Please sign in using your provided credentials.</p>
        </div>

        {{-- Error alerts --}}
        @if($errors->any())
        <div style="background:rgba(220,38,38,0.12); border:1px solid rgba(220,38,38,0.28); border-radius:10px; padding:12px 15px; margin-bottom:18px; color:#f87171; font-size:13px; line-height:1.5;">
            @foreach($errors->all() as $error)
                <div>&bull; {{ $error }}</div>
            @endforeach
        </div>
        @endif

        {{-- Form --}}
        <form method="POST" action="{{ route('registration.store') }}" style="display:flex; flex-direction:column; gap:14px;">
            @csrf

            {{-- Student ID --}}
            <div>
                <label style="display:block; color:rgba(255,255,255,0.55); font-size:10px; letter-spacing:2px; text-transform:uppercase; margin-bottom:7px;">Student ID</label>
                <div style="position:relative;">
                    <div style="position:absolute; left:13px; top:50%; transform:translateY(-50%); color:#8b5cf6; pointer-events:none;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                            <rect x="2" y="5" width="20" height="14" rx="2" stroke="currentColor" stroke-width="2"/>
                            <path d="M2 10h20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <input type="text" name="student_id" value="{{ old('student_id') }}" placeholder="e.g. DEMO000001"
                        class="ht-input"
                        style="width:100%; background:rgba(255,255,255,0.07); border:1px solid rgba(255,255,255,0.13); border-radius:10px; padding:11px 14px 11px 40px; color:white; font-size:14px; outline:none; transition:border-color 0.2s, box-shadow 0.2s;">
                </div>
            </div>

            {{-- Username --}}
            <div>
                <label style="display:block; color:rgba(255,255,255,0.55); font-size:10px; letter-spacing:2px; text-transform:uppercase; margin-bottom:7px;">Username</label>
                <div style="position:relative;">
                    <div style="position:absolute; left:13px; top:50%; transform:translateY(-50%); color:#8b5cf6; pointer-events:none;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </div>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Create your username"
                        class="ht-input"
                        style="width:100%; background:rgba(255,255,255,0.07); border:1px solid rgba(255,255,255,0.13); border-radius:10px; padding:11px 14px 11px 40px; color:white; font-size:14px; outline:none; transition:border-color 0.2s, box-shadow 0.2s;">
                </div>
            </div>

            {{-- Email --}}
            <div>
                <label style="display:block; color:rgba(255,255,255,0.55); font-size:10px; letter-spacing:2px; text-transform:uppercase; margin-bottom:7px;">Email Address</label>
                <div style="position:relative;">
                    <div style="position:absolute; left:13px; top:50%; transform:translateY(-50%); color:#8b5cf6; pointer-events:none;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <polyline points="22,6 12,13 2,6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="your@email.com"
                        class="ht-input"
                        style="width:100%; background:rgba(255,255,255,0.07); border:1px solid rgba(255,255,255,0.13); border-radius:10px; padding:11px 14px 11px 40px; color:white; font-size:14px; outline:none; transition:border-color 0.2s, box-shadow 0.2s;">
                </div>
            </div>

            {{-- Password row --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div>
                    <label style="display:block; color:rgba(255,255,255,0.55); font-size:10px; letter-spacing:2px; text-transform:uppercase; margin-bottom:7px;">Password</label>
                    <div style="position:relative;">
                        <div style="position:absolute; left:13px; top:50%; transform:translateY(-50%); color:#8b5cf6; pointer-events:none;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke="currentColor" stroke-width="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <input type="password" name="password" placeholder="••••••••"
                            class="ht-input"
                            style="width:100%; background:rgba(255,255,255,0.07); border:1px solid rgba(255,255,255,0.13); border-radius:10px; padding:11px 14px 11px 40px; color:white; font-size:14px; outline:none; transition:border-color 0.2s, box-shadow 0.2s;">
                    </div>
                </div>
                <div>
                    <label style="display:block; color:rgba(255,255,255,0.55); font-size:10px; letter-spacing:2px; text-transform:uppercase; margin-bottom:7px;">Confirm</label>
                    <div style="position:relative;">
                        <div style="position:absolute; left:13px; top:50%; transform:translateY(-50%); color:#8b5cf6; pointer-events:none;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke="currentColor" stroke-width="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <input type="password" name="password_confirmation" placeholder="••••••••"
                            class="ht-input"
                            style="width:100%; background:rgba(255,255,255,0.07); border:1px solid rgba(255,255,255,0.13); border-radius:10px; padding:11px 14px 11px 40px; color:white; font-size:14px; outline:none; transition:border-color 0.2s, box-shadow 0.2s;">
                    </div>
                </div>
            </div>

            {{-- Sign Up button --}}
            <button type="submit" class="signup-btn"
                style="width:100%; background:linear-gradient(135deg,#7c3aed,#6d28d9); border:none; border-radius:10px; padding:13px; color:white; font-size:15px; font-weight:600; cursor:pointer; letter-spacing:0.5px; box-shadow:0 8px 24px rgba(124,58,237,0.40); margin-top:2px;">
                Create Account &rarr;
            </button>

            {{-- OR divider --}}
            <div style="display:flex; align-items:center; gap:12px; margin:2px 0;">
                <div style="flex:1; height:1px; background:rgba(255,255,255,0.10);"></div>
                <span style="color:rgba(255,255,255,0.32); font-size:12px; letter-spacing:1.5px;">OR</span>
                <div style="flex:1; height:1px; background:rgba(255,255,255,0.10);"></div>
            </div>

            {{-- Back to login --}}
            <p style="text-align:center; color:rgba(255,255,255,0.42); font-size:13px; margin:0; line-height:1.5;">
                Already have an account?
                <a href="{{ route('login') }}" style="color:#a78bfa; text-decoration:none; font-weight:500; transition:color 0.2s;"
                   onmouseover="this.style.color='#c4b5fd'" onmouseout="this.style.color='#a78bfa'">Sign In</a>
            </p>
        </form>
    </div>

</div>

@endsection
