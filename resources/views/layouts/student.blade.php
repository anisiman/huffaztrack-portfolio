<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title') — HuffazTrack</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    @stack('styles')
    <style>
        body { font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 14px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 500;
            color: #64748b;
            text-decoration: none;
            transition: background 0.15s, color 0.15s;
            cursor: pointer;
            width: 100%;
        }
        .nav-item:hover { background: #f1f5f9; color: #334155; }
        .nav-item.active {
            background: rgba(124,58,237,0.1);
            color: #6d28d9;
            font-weight: 600;
            position: relative;
        }
        .nav-item.active::before {
            content: '';
            position: absolute;
            left: 0; top: 50%;
            transform: translateY(-50%);
            width: 3px; height: 20px;
            background: #7c3aed;
            border-radius: 0 3px 3px 0;
        }
        .nav-item svg { width: 18px; height: 18px; flex-shrink: 0; opacity: 0.7; }
        .nav-item.active svg { opacity: 1; }

        .nav-section-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #94a3b8;
            padding: 0 14px;
            margin: 18px 0 6px;
        }

        #student-sidebar {
            overflow: hidden;
            transition: transform 0.3s ease, width 0.3s ease, min-width 0.3s ease;
        }
        @media (max-width: 1023px) {
            #student-sidebar { position: fixed; top: 0; left: 0; height: 100%; z-index: 50; }
            #student-sidebar.sidebar-closed { transform: translateX(-100%); }
        }
        @media (min-width: 1024px) {
            #student-sidebar.sidebar-closed { width: 0; min-width: 0; }
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-900">

<div class="flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside id="student-sidebar"
           class="flex flex-col w-64 bg-white border-r border-slate-100 h-full flex-shrink-0
                  fixed lg:relative inset-y-0 left-0 z-50">

        <!-- Shared logo partial -->
        @include('partials.sidebar-logo')

        <!-- Navigation -->
        <nav class="flex-1 px-3 pt-2 pb-4 overflow-y-auto">

            <p class="nav-section-label">Main</p>

            <a href="{{ route('student.dashboard') }}"
               class="nav-item {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                    <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                    <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                    <rect x="14" y="14" width="7" height="7" rx="1.5"/>
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('student.attendance') }}"
               class="nav-item {{ request()->routeIs('student.attendance') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="3" y="4" width="18" height="18" rx="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M16 2v4M8 2v4M3 10h18" stroke-linecap="round"/>
                    <path d="M9 16l2 2 4-4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Tasmik Attendance</span>
            </a>

            <a href="{{ route('student.subjectAttendance') }}"
               class="nav-item {{ request()->routeIs('student.subjectAttendance*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M3 4.5h18M3 9h18M3 13.5h18M3 18h18" stroke-linecap="round"/>
                </svg>
                <span>Subject Attendance</span>
            </a>

            <a href="{{ route('student.hafazan') }}"
               class="nav-item {{ request()->routeIs('student.hafazan') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M8 7h8M8 11h5" stroke-linecap="round"/>
                </svg>
                <span>My Hafazan</span>
            </a>

            <a href="{{ route('student.subjects') }}"
               class="nav-item {{ request()->routeIs('student.subjects*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9 7h6M9 11h6" stroke-linecap="round"/>
                </svg>
                <span>My Subjects</span>
            </a>

            <a href="{{ route('student.sectionEnrollment') }}"
               class="nav-item {{ request()->routeIs('student.sectionEnrollment*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M9 12h3.75M9 15h3.75M9 18h3.75M4.5 3.75h9l4.5 4.5v12a1.5 1.5 0 0 1-1.5 1.5h-12a1.5 1.5 0 0 1-1.5-1.5V5.25a1.5 1.5 0 0 1 1.5-1.5Z" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Section Enrollment</span>
            </a>

            <a href="{{ route('student.notes') }}"
               class="nav-item {{ request()->routeIs('student.notes') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Lecturer Notes</span>
            </a>

            <p class="nav-section-label">Account</p>

            <a href="{{ route('profile.show') }}"
               class="nav-item {{ request()->routeIs('profile.show') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="8" r="4" stroke-linecap="round"/>
                    <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>My Profile</span>
            </a>

            <a href="{{ route('student.biodata') }}"
               class="nav-item {{ request()->routeIs('student.biodata') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="3" y="5" width="18" height="14" rx="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="12" cy="10" r="2.5" stroke-linecap="round"/>
                    <path d="M8 17c1.333-1.667 2.667-1.667 4 0" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>My Biodata</span>
            </a>

        </nav>

        <!-- Bottom: user info + logout -->
        <div class="border-t border-slate-100 px-3 py-4 space-y-1">
            <a href="{{ route('profile.show') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-slate-50 transition">
                <div class="w-8 h-8 rounded-full bg-violet-100 flex items-center justify-center flex-shrink-0 overflow-hidden">
                    @if(Auth::user()->profile_picture)
                        <img src="{{ asset('storage/' . Auth::user()->profile_picture) }}" alt="" class="w-full h-full object-cover">
                    @else
                        <span class="text-violet-700 font-bold text-sm">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-800 truncate">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-slate-400">Student</p>
                </div>
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-item text-red-500 hover:!bg-red-50 hover:!text-red-600">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" stroke-linecap="round" stroke-linejoin="round"/>
                        <polyline points="16,17 21,12 16,7" stroke-linecap="round" stroke-linejoin="round"/>
                        <line x1="21" y1="12" x2="9" y2="12" stroke-linecap="round"/>
                    </svg>
                    <span>Logout</span>
                </button>
            </form>
        </div>

    </aside>

    <!-- Mobile overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/20 backdrop-blur-sm z-40 hidden"></div>

    <!-- MAIN CONTENT -->
    <div class="flex-1 flex flex-col min-w-0">

        <!-- Navbar -->
        <header class="flex items-center justify-between border-b border-slate-100 bg-white px-6 py-4 flex-shrink-0">

            <!-- Hamburger -->
            <button id="sidebar-toggle"
                    class="rounded-full p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                     stroke-width="1.8" stroke="currentColor" class="h-6 w-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                </svg>
            </button>

            <!-- Right: notification + profile -->
            <div class="flex items-center gap-3">

                <!-- Notification bell -->
                @include('partials.notification-bell')

                <!-- Profile dropdown -->
                <div class="relative">
                    <button id="profile-btn"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 transition hover:bg-slate-100">
                        <div class="w-9 h-9 rounded-full bg-violet-100 flex items-center justify-center flex-shrink-0 overflow-hidden">
                            @if(Auth::user()->profile_picture)
                                <img src="{{ asset('storage/' . Auth::user()->profile_picture) }}" alt="" class="w-full h-full object-cover">
                            @else
                                <span class="text-violet-700 font-bold text-sm">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                            @endif
                        </div>
                        <div class="text-left hidden sm:block">
                            <p class="text-sm font-semibold text-slate-700 leading-tight">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-slate-400">Student</p>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                             stroke-width="1.8" stroke="currentColor" class="h-4 w-4 text-slate-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                        </svg>
                    </button>
                    @include('partials.profile-dropdown')
                </div>

            </div>
        </header>

        <!-- Page content -->
        <div class="flex-1 overflow-y-auto">
            <main class="min-h-full p-6 lg:p-8">
                @yield('content')
            </main>
        </div>

    </div>

</div>

<script>
(function() {
    const sidebar = document.getElementById('student-sidebar');
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
})();
</script>

@stack('scripts')
</body>
</html>
