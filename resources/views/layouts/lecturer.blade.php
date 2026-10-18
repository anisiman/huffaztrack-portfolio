<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://cdn.tailwindcss.com"></script>
    @stack('styles')
    <style>
        /* ── Sidebar base ── */
        #lecturer-sidebar {
            overflow: hidden;
            transition: transform 0.3s ease, width 0.3s ease, min-width 0.3s ease;
        }
        @media (max-width: 1023px) {
            #lecturer-sidebar {
                position: fixed;
                top: 0; left: 0;
                height: 100%;
                z-index: 50;
            }
            #lecturer-sidebar.sidebar-closed { transform: translateX(-100%); }
        }
        @media (min-width: 1024px) {
            #lecturer-sidebar.sidebar-closed { width: 0; min-width: 0; }
        }

        /* ── Nav item ── */
        .sb-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 11px;
            border-radius: 9px;
            font-size: 13.5px;
            font-weight: 500;
            color: #64748b;
            text-decoration: none;
            transition: background 0.15s, color 0.15s;
            position: relative;
            white-space: nowrap;
        }
        .sb-item:hover {
            background: #f1f5f9;
            color: #334155;
        }
        .sb-item--active {
            background: rgba(124,58,237,0.1);
            color: #6d28d9;
            font-weight: 600;
        }
        .sb-item--active::before {
            content: '';
            position: absolute;
            left: 0; top: 50%;
            transform: translateY(-50%);
            width: 3px; height: 20px;
            background: #7c3aed;
            border-radius: 0 3px 3px 0;
        }
        .sb-item--logout { width: 100%; border: none; background: none; text-align: left; cursor: pointer; }
        .sb-item--logout:hover { background: rgba(239,68,68,0.08) !important; color: #ef4444 !important; }

        /* ── Section label ── */
        .sb-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.11em;
            text-transform: uppercase;
            color: #94a3b8;
            padding: 0 11px;
            margin-bottom: 4px;
        }

        /* ── User card ── */
        .sb-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 11px;
            border-radius: 10px;
            background: #f8fafc;
            margin-bottom: 6px;
            overflow: hidden;
        }
        .sb-user__avatar {
            width: 32px; height: 32px;
            border-radius: 8px;
            background: linear-gradient(135deg, #7c3aed, #5b21b6);
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700; color: #fff;
            flex-shrink: 0;
        }
        .sb-user__name  { font-size: 12.5px; font-weight: 600; color: #1e293b; line-height: 1.25; }
        .sb-user__role  { font-size: 10.5px; color: #94a3b8; margin-top: 1px; }

        /* ── Sidebar divider ── */
        .sb-divider { border-top: 1px solid #f1f5f9; margin: 0; }
    </style>
</head>
<body class="bg-[#f4f6fb] font-sans text-slate-800">
<div class="flex min-h-screen">

    {{-- ── SIDEBAR ── --}}
    <aside id="lecturer-sidebar"
           class="flex flex-col w-64 flex-shrink-0"
           style="background:#fff; border-right:1px solid #f1f5f9;">

        {{-- Logo --}}
        <div class="flex items-center gap-3 px-5 py-5 flex-shrink-0 sb-divider" style="border-top:none; border-bottom:1px solid #f1f5f9;">
            <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#7c3aed,#4c1d95);box-shadow:0 4px 14px rgba(124,58,237,0.3);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div>
                <p style="font-size:14px;font-weight:700;color:#1e293b;line-height:1.2;letter-spacing:-0.01em;">HuffazTrack</p>
                <p style="font-size:9.5px;font-weight:600;letter-spacing:0.13em;text-transform:uppercase;color:#94a3b8;">Management</p>
            </div>
        </div>

        {{-- Nav --}}
        <div class="flex-1 overflow-y-auto" style="padding:18px 10px 8px;">

            {{-- Overview --}}
            <div style="margin-bottom:22px;">
                <p class="sb-label">Overview</p>
                <a href="{{ route('lecturer.dashboard') }}"
                   class="sb-item {{ request()->routeIs('lecturer.dashboard') ? 'sb-item--active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" style="width:17px;height:17px;flex-shrink:0;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                    </svg>
                    <span>Dashboard</span>
                </a>
            </div>

            {{-- Academic --}}
            <div style="margin-bottom:22px;">
                <p class="sb-label">Academic</p>
                <a href="{{ route('lecturer.students') }}"
                   class="sb-item {{ request()->routeIs('lecturer.students*') ? 'sb-item--active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" style="width:17px;height:17px;flex-shrink:0;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                    <span>Students</span>
                </a>
                <a href="{{ route('lecturer.hifz') }}"
                   class="sb-item {{ request()->routeIs('lecturer.hifz') ? 'sb-item--active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" style="width:17px;height:17px;flex-shrink:0;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                    </svg>
                    <span>Hafazan</span>
                </a>
                {{-- Attendance (collapsible: Tasmik Attendance, Excuse Letters, Subject Attendance) --}}
                @php
                    $attOpen = request()->routeIs('lecturer.attendance*')
                        || request()->routeIs('lecturer.subjectAttendance*');
                @endphp
                <div>
                    <button type="button" onclick="toggleAtt()"
                        class="sb-item w-full {{ $attOpen ? 'sb-item--active' : '' }}"
                        style="border:none; background:none; cursor:pointer;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" style="width:17px;height:17px;flex-shrink:0;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                        </svg>
                        <span>Attendance</span>
                        <svg id="att-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                            class="ml-auto transition-transform duration-200 {{ $attOpen ? 'rotate-180' : '' }}"
                            style="width:13px; height:13px; flex-shrink:0;">
                            <polyline points="6,9 12,15 18,9" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>

                    <div id="att-submenu" class="{{ $attOpen ? 'flex' : 'hidden' }} flex-col pl-3 mt-0.5 gap-0.5">
                        <a href="{{ route('lecturer.attendance') }}"
                           class="sb-item {{ request()->routeIs('lecturer.attendance') ? 'sb-item--active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" style="width:15px;height:15px;flex-shrink:0;">
                                <rect x="3" y="4" width="18" height="18" rx="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M16 2v4M8 2v4M3 10h18" stroke-linecap="round" />
                                <path d="M9 16l2 2 4-4" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span>Tasmik Attendance</span>
                        </a>
                        <a href="{{ route('lecturer.attendance.appeals') }}"
                           class="sb-item {{ request()->routeIs('lecturer.attendance.appeals*') ? 'sb-item--active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" style="width:15px;height:15px;flex-shrink:0;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                            <span>Excuse Letters</span>
                        </a>
                        <a href="{{ route('lecturer.subjectAttendance') }}"
                           class="sb-item {{ request()->routeIs('lecturer.subjectAttendance*') ? 'sb-item--active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" style="width:15px;height:15px;flex-shrink:0;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h18M3 9h18M3 13.5h18M3 18h18" />
                            </svg>
                            <span>Subject Attendance</span>
                        </a>
                    </div>
                </div>
                <a href="{{ route('lecturer.subjects') }}"
                   class="sb-item {{ request()->routeIs('lecturer.subjects*') ? 'sb-item--active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" style="width:17px;height:17px;flex-shrink:0;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s4.332.477 5.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span>Subjects</span>
                </a>
                <a href="{{ route('lecturer.sections') }}"
                   class="sb-item {{ request()->routeIs('lecturer.sections*') ? 'sb-item--active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" style="width:17px;height:17px;flex-shrink:0;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75M4.5 3.75h9l4.5 4.5v12a1.5 1.5 0 0 1-1.5 1.5h-12a1.5 1.5 0 0 1-1.5-1.5V5.25a1.5 1.5 0 0 1 1.5-1.5Z" />
                    </svg>
                    <span>My Sections</span>
                </a>
            </div>

            {{-- Insights --}}
            <div>
                <p class="sb-label">Insights</p>
                <a href="{{ route('lecturer.reports') }}"
                   class="sb-item {{ request()->routeIs('lecturer.reports*') ? 'sb-item--active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" style="width:17px;height:17px;flex-shrink:0;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 0 0-1.883 2.542l.857 6a2.25 2.25 0 0 0 2.227 1.932H19.05a2.25 2.25 0 0 0 2.227-1.932l.857-6a2.25 2.25 0 0 0-1.883-2.542m-16.5 0V6A2.25 2.25 0 0 1 6 3.75h3.879a1.5 1.5 0 0 1 1.06.44l2.122 2.12a1.5 1.5 0 0 0 1.06.44H18A2.25 2.25 0 0 1 20.25 9v.776" />
                    </svg>
                    <span>Reports</span>
                </a>
                <a href="{{ route('lecturer.analytics') }}"
                   class="sb-item {{ request()->routeIs('lecturer.analytics') ? 'sb-item--active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" style="width:17px;height:17px;flex-shrink:0;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                    </svg>
                    <span>Analytics</span>
                </a>
               
            </div>

        </div>

        {{-- Footer: user card + settings + logout --}}
        <div style="border-top:1px solid #f1f5f9; padding:12px 10px;">

            {{-- User mini card --}}
            <a href="{{ route('profile.show') }}" class="sb-user" style="text-decoration:none;">
                <div class="sb-user__avatar" style="overflow:hidden; padding:0;">
                    @if(Auth::user()->profile_picture)
                        <img src="{{ asset('storage/' . Auth::user()->profile_picture) }}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:8px;">
                    @else
                        <span style="font-size:12px;font-weight:700;color:#fff;">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                    @endif
                </div>
                <div style="min-width:0;">
                    <p class="sb-user__name truncate">{{ Auth::user()->name }}</p>
                    <p class="sb-user__role">Lecturer</p>
                </div>
            </a>

        {{-- settings deleted --}}

            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button type="submit" class="sb-item sb-item--logout">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" style="width:17px;height:17px;flex-shrink:0;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25" />
                    </svg>
                    <span>Log Out</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- ── MAIN AREA ── --}}
    <div class="flex flex-1 flex-col min-w-0">

        {{-- Top bar --}}
        <header class="flex items-center justify-between border-b border-slate-200 bg-white px-6 py-4 flex-shrink-0">
            <button id="sidebar-toggle"
                    class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>

            {{-- Right: bell + profile dropdown --}}
            <div class="flex items-center gap-2">
                @include('partials.notification-bell')
                <div class="relative">
                <button id="profile-btn"
                    class="flex items-center gap-3 rounded-xl px-3 py-2 transition hover:bg-slate-100">
                    <div class="w-8 h-8 rounded-full bg-violet-100 flex items-center justify-center flex-shrink-0 overflow-hidden">
                        @if(Auth::user()->profile_picture)
                            <img src="{{ asset('storage/' . Auth::user()->profile_picture) }}" alt="" class="w-full h-full object-cover">
                        @else
                            <span class="text-violet-700 font-bold text-xs">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                        @endif
                    </div>
                    <div class="text-left hidden sm:block">
                        <p class="text-sm font-semibold text-slate-700 leading-tight">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-slate-400">Lecturer</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.8" stroke="currentColor" class="h-4 w-4 text-slate-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>
                @include('partials.profile-dropdown')
                </div>
            </div>{{-- end flex bell+profile --}}
        </header>

        <main class="flex-1 p-6 md:p-8 overflow-auto">
            @yield('content')
        </main>
    </div>

</div>

@yield('scripts')

<div id="sidebar-overlay" class="fixed inset-0 bg-black/30 z-40 hidden"></div>
<script>
    function toggleAtt() {
        const menu = document.getElementById('att-submenu');
        const chevron = document.getElementById('att-chevron');
        menu.classList.toggle('hidden');
        menu.classList.toggle('flex');
        chevron.classList.toggle('rotate-180');
    }
</script>
<script>
(function() {
    const sidebar = document.getElementById('lecturer-sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const toggle  = document.getElementById('sidebar-toggle');
    if (!sidebar || !toggle) return;

    if (window.innerWidth < 1024) sidebar.classList.add('sidebar-closed');

    toggle.addEventListener('click', function() {
        sidebar.classList.toggle('sidebar-closed');
        if (overlay) {
            const isOpen = !sidebar.classList.contains('sidebar-closed');
            overlay.classList.toggle('hidden', !(window.innerWidth < 1024 && isOpen));
        }
    });

    if (overlay) {
        overlay.addEventListener('click', function() {
            sidebar.classList.add('sidebar-closed');
            overlay.classList.add('hidden');
        });
    }

    window.addEventListener('resize', function() {
        if (window.innerWidth >= 1024) {
            sidebar.classList.remove('sidebar-closed');
            if (overlay) overlay.classList.add('hidden');
        }
    });
}());
</script>
</body>
</html>
