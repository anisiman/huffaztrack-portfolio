<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') — HuffazTrack</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js"></script>
    @stack('styles')
    <style>
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        /* Sidebar nav item base */
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

        .nav-item:hover {
            background: #f1f5f9;
            color: #334155;
        }

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

        .nav-item svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            opacity: 0.7;
        }

        .nav-item.active svg {
            opacity: 1;
        }

        /* Section label */
        .nav-section-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #94a3b8;
            padding: 0 14px;
            margin: 18px 0 6px;
        }

        /* Scrollbar — targets inner nav since aside uses overflow:hidden */
        #admin-sidebar nav::-webkit-scrollbar {
            width: 4px;
        }

        #admin-sidebar nav::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 4px;
        }

        /* ── Sidebar toggle ── */
        #admin-sidebar {
            overflow: hidden;
            transition: transform 0.3s ease, width 0.3s ease, min-width 0.3s ease;
        }
        /* Mobile: slide in/out */
        @media (max-width: 1023px) {
            #admin-sidebar.sidebar-closed {
                transform: translateX(-100%);
            }
        }
        /* Desktop: collapse width so main content expands */
        @media (min-width: 1024px) {
            #admin-sidebar.sidebar-closed {
                width: 0;
                min-width: 0;
            }
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-900">

    @php
        $sidebarStudentCount = \App\Models\Student::count();
    @endphp

    <div class="flex h-screen overflow-hidden">

        {{-- ══ SIDEBAR ══ --}}
        <aside id="admin-sidebar"
            class="flex flex-col w-64 bg-white border-r border-slate-100 h-full flex-shrink-0
                  fixed lg:relative inset-y-0 left-0 z-50">

            {{-- Logo (shared partial) --}}
            @include('partials.sidebar-logo')

            {{-- Navigation --}}
            <nav class="flex-1 px-3 pt-2 pb-4 overflow-y-auto">

                {{-- MAIN section --}}
                <p class="nav-section-label">Main</p>

                <a href="{{ route('admin.dashboard') }}"
                    class="nav-item {{ request()->routeIs('admin.dashboard') || request()->routeIs('admin.dashboard.*') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="3" y="3" width="7" height="7" rx="1.5" />
                        <rect x="14" y="3" width="7" height="7" rx="1.5" />
                        <rect x="3" y="14" width="7" height="7" rx="1.5" />
                        <rect x="14" y="14" width="7" height="7" rx="1.5" />
                    </svg>
                    <span>Dashboard</span>
                </a>

                <!-- Masterlist -->
                <a href="{{ route('admin.masterlist') }}"
                    class="nav-item {{ request()->routeIs('admin.masterlist*') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <line x1="8" y1="6" x2="21" y2="6" stroke-linecap="round" />
                        <line x1="8" y1="12" x2="21" y2="12" stroke-linecap="round" />
                        <line x1="8" y1="18" x2="21" y2="18" stroke-linecap="round" />
                        <line x1="3" y1="6" x2="3.01" y2="6" stroke-linecap="round"
                            stroke-width="2.5" />
                        <line x1="3" y1="12" x2="3.01" y2="12" stroke-linecap="round"
                            stroke-width="2.5" />
                        <line x1="3" y1="18" x2="3.01" y2="18" stroke-linecap="round"
                            stroke-width="2.5" />
                    </svg>
                    <span>Masterlist</span>
                </a>

                <a href="{{ route('admin.lecturers') }}"
                    class="nav-item {{ request()->routeIs('admin.lecturers*') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke-linecap="round"
                            stroke-linejoin="round" />
                        <circle cx="9" cy="7" r="4" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M16 3.13a4 4 0 0 1 0 7.75" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span>Halaqah</span>
                </a>

                <a href="{{ route('admin.hafazan') }}"
                    class="nav-item {{ request()->routeIs('admin.hafazan') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" stroke-linecap="round"
                            stroke-linejoin="round" />
                        <path d="M8 7h8M8 11h5" stroke-linecap="round" />
                    </svg>
                    <span>Hafazan</span>
                </a>

                {{-- Attendance (collapsible) --}}
                @php $attOpen = request()->routeIs('admin.attendance*') || request()->routeIs('admin.subjectAttendance*'); @endphp
                <div>
                    <button type="button" onclick="toggleAtt()"
                        class="nav-item w-full {{ $attOpen ? 'bg-violet-50 text-violet-700' : '' }}"
                        style="border:none; background: {{ $attOpen ? '#f5f3ff' : 'none' }};">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="3" y="4" width="18" height="18" rx="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <path d="M16 2v4M8 2v4M3 10h18" stroke-linecap="round" />
                            <path d="M9 16l2 2 4-4" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <span>Attendance</span>
                        <svg id="att-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2"
                            class="ml-auto transition-transform duration-200 {{ $attOpen ? 'rotate-180' : '' }}"
                            style="width:14px; height:14px; flex-shrink:0;">
                            <polyline points="6,9 12,15 18,9" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>

                    <div id="att-submenu" class="{{ $attOpen ? '' : 'hidden' }} pl-4 mt-0.5 space-y-0.5">
                        <a href="{{ route('admin.attendance') }}"
                           class="nav-item {{ request()->routeIs('admin.attendance*') ? 'active' : '' }}">
                            <span>Tasmik Attendance</span>
                        </a>
                        <a href="{{ route('admin.subjectAttendance') }}"
                           class="nav-item {{ request()->routeIs('admin.subjectAttendance*') ? 'active' : '' }}">
                            <span>Subject Attendance</span>
                        </a>
                    </div>
                </div>

                {{-- Subject Management (collapsible: Subjects, Sections) --}}
                @php $subjOpen = request()->routeIs('admin.subjects*') || request()->routeIs('admin.sections*'); @endphp
                <div>
                    <button type="button" onclick="toggleSubj()"
                        class="nav-item w-full {{ $subjOpen ? 'bg-violet-50 text-violet-700' : '' }}"
                        style="border:none; background: {{ $subjOpen ? '#f5f3ff' : 'none' }};">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <path d="M9 7h6M9 11h6" stroke-linecap="round" />
                        </svg>
                        <span>Subjects</span>
                        <svg id="subj-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2"
                            class="ml-auto transition-transform duration-200 {{ $subjOpen ? 'rotate-180' : '' }}"
                            style="width:14px; height:14px; flex-shrink:0;">
                            <polyline points="6,9 12,15 18,9" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>

                    <div id="subj-submenu" class="{{ $subjOpen ? '' : 'hidden' }} pl-4 mt-0.5 space-y-0.5">
                        <a href="{{ route('admin.subjects') }}"
                           class="nav-item {{ request()->routeIs('admin.subjects*') ? 'active' : '' }}">
                            <span>Subjects</span>
                        </a>
                        <a href="{{ route('admin.sections') }}"
                           class="nav-item {{ request()->routeIs('admin.sections*') ? 'active' : '' }}">
                            <span>Sections</span>
                        </a>
                    </div>
                </div>

                {{-- REPORTS section --}}
                <p class="nav-section-label">Reports</p>

                {{-- ANALYTICS --}}
                <a href="{{ route('admin.analytics') }}"
                    class="nav-item {{ request()->routeIs('admin.analytics') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M3 3v18h18" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M7 16l4-4 4 4 4-5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span>Analytics</span>
                </a>

                {{-- Report (collapsible) --}}
                @php
                    $repAttOpen = request()->routeIs('admin.reports.attendance*')
                        || request()->routeIs('admin.reports.subjectAttendance*')
                        || (request()->routeIs('admin.reports') && request()->query('section') === 'attendance');
                    $repHfzOpen = request()->routeIs('admin.reports.hafazan*')
                        || (request()->routeIs('admin.reports') && request()->query('section') === 'hafazan');
                    $repOpen = $repAttOpen || $repHfzOpen || (request()->routeIs('admin.reports') && ! request()->filled('section'));
                @endphp
                <div>
                    <button type="button" onclick="toggleRep()"
                        class="nav-item w-full {{ $repOpen ? 'bg-violet-50 text-violet-700' : '' }}"
                        style="border:none; background: {{ $repOpen ? '#f5f3ff' : 'none' }};">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <polyline points="14,2 14,8 20,8" stroke-linecap="round" stroke-linejoin="round" />
                            <line x1="16" y1="13" x2="8" y2="13" stroke-linecap="round" />
                            <line x1="16" y1="17" x2="8" y2="17" stroke-linecap="round" />
                            <polyline points="10,9 9,9 8,9" stroke-linecap="round" />
                        </svg>
                        <span>Report</span>
                        <svg id="rep-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2"
                            class="ml-auto transition-transform duration-200 {{ $repOpen ? 'rotate-180' : '' }}"
                            style="width:14px; height:14px; flex-shrink:0;">
                            <polyline points="6,9 12,15 18,9" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>

                    <div id="rep-submenu" class="{{ $repOpen ? '' : 'hidden' }} pl-4 mt-0.5 space-y-0.5">
                        <a href="{{ route('admin.reports', ['section' => 'attendance']) }}"
                           class="nav-item {{ $repAttOpen ? 'active' : '' }}">
                            <span>Attendance</span>
                        </a>
                        <a href="{{ route('admin.reports', ['section' => 'hafazan']) }}"
                           class="nav-item {{ $repHfzOpen ? 'active' : '' }}">
                            <span>Hafazan</span>
                        </a>
                    </div>
                </div>

                {{-- SYSTEM section --}}
                <p class="nav-section-label">System</p>

                {{-- User Management (collapsible) --}}
                @php $umOpen = request()->routeIs('admin.students*') || request()->routeIs('admin.lecturerAccounts*') || request()->routeIs('admin.adminAccounts*'); @endphp
                <div>
                    <button type="button" onclick="toggleUM()"
                        class="nav-item w-full {{ $umOpen ? 'bg-violet-50 text-violet-700' : '' }}"
                        style="border:none; background: {{ $umOpen ? '#f5f3ff' : 'none' }};">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <circle cx="9" cy="7" r="4" stroke-linecap="round" />
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke-linecap="round" />
                        </svg>
                        <span>User Management</span>
                        <svg id="um-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2"
                            class="ml-auto transition-transform duration-200 {{ $umOpen ? 'rotate-180' : '' }}"
                            style="width:14px; height:14px; flex-shrink:0;">
                            <polyline points="6,9 12,15 18,9" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>

                    <div id="um-submenu" class="{{ $umOpen ? '' : 'hidden' }} pl-4 mt-0.5 space-y-0.5">
                    <a href="{{ route('admin.adminAccounts') }}"
                       class="nav-item {{ request()->routeIs('admin.adminAccounts*') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M12 2a4 4 0 1 0 0 8 4 4 0 0 0 0-8z" stroke-linecap="round"/>
                            <path d="M16 14H8a4 4 0 0 0-4 4v1h16v-1a4 4 0 0 0-4-4z" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M17 8l1.5 1.5L21 7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>Admin Accounts</span>
                    </a>

                    <a href="{{ route('admin.lecturerAccounts') }}"
                       class="nav-item {{ request()->routeIs('admin.lecturerAccounts*') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <circle cx="12" cy="8" r="4" stroke-linecap="round"/>
                            <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>Lecturer Accounts</span>
                    </a>

                    <a href="{{ route('admin.students') }}"
                       class="nav-item {{ request()->routeIs('admin.students*') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="3" y="5" width="18" height="14" rx="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M8 10h8M8 14h5" stroke-linecap="round"/>
                        </svg>
                        <span>Student Accounts</span>
                    </a>
                    
                    </div>
                </div>

                {{-- Batch Management --}}
                <a href="{{ route('admin.batches.index') }}"
                    class="nav-item {{ request()->routeIs('admin.batches*') ? 'active' : '' }}">

                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="3" y="4" width="18" height="16" rx="2" stroke-linecap="round"
                            stroke-linejoin="round" />

                        <path d="M8 2v4M16 2v4M3 10h18" stroke-linecap="round" stroke-linejoin="round" />

                        <path d="M8 14h8M8 18h5" stroke-linecap="round" />
                    </svg>

                    <span>Batch Management</span>
                </a>

                {{-- Academic Period Management --}}
                <a href="{{ route('admin.academic-periods.index') }}"
                    class="nav-item {{ request()->routeIs('admin.academic-periods*') ? 'active' : '' }}">

                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="3" y="4" width="18" height="16" rx="2" stroke-linecap="round"
                            stroke-linejoin="round" />

                        <path d="M8 2v4M16 2v4M3 10h18" stroke-linecap="round" stroke-linejoin="round" />

                        <path d="M12 14v4M10 16h4" stroke-linecap="round" />
                    </svg>

                    <span>Academic Semesters</span>
                </a>

                {{--Settings deleted--}}
            </nav>

            {{-- User profile + logout --}}
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
                        <p class="text-xs text-slate-400">Administrator</p>
                    </div>
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-item text-red-500 hover:!bg-red-50 hover:!text-red-600">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <polyline points="16,17 21,12 16,7" stroke-linecap="round" stroke-linejoin="round" />
                            <line x1="21" y1="12" x2="9" y2="12"
                                stroke-linecap="round" />
                        </svg>
                        <span>Logout</span>
                    </button>
                </form>
            </div>

        </aside>

        {{-- Mobile overlay --}}
        <div id="sidebar-overlay" class="fixed inset-0 bg-black/20 backdrop-blur-sm z-40 hidden"></div>

        {{-- ══ MAIN CONTENT ══ --}}
        <div class="flex-1 flex flex-col min-w-0">

            {{-- Header with hamburger + profile dropdown --}}
            <header class="flex items-center justify-between border-b border-slate-100 bg-white px-6 py-4 flex-shrink-0">

                {{-- Left: sidebar toggle --}}
                <button id="sidebar-toggle"
                    class="rounded-full p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                    aria-label="Toggle sidebar">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.8" stroke="currentColor" class="h-6 w-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                {{-- Right: bell + profile dropdown --}}
                <div class="flex items-center gap-2">
                    @include('partials.notification-bell')
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
                            <p class="text-xs text-slate-400">Administrator</p>
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

            {{-- Page content (only this scrolls) --}}
            <div class="flex-1 overflow-y-auto">
                <main class="min-h-full p-6 lg:p-8">
                    @yield('content')
                </main>
            </div>

        </div>

    </div>

    <script>
        function toggleUM() {
            const menu = document.getElementById('um-submenu');
            const chevron = document.getElementById('um-chevron');
            menu.classList.toggle('hidden');
            chevron.classList.toggle('rotate-180');
        }

        function toggleAtt() {
            const menu = document.getElementById('att-submenu');
            const chevron = document.getElementById('att-chevron');
            menu.classList.toggle('hidden');
            chevron.classList.toggle('rotate-180');
        }

        function toggleRep() {
            const menu = document.getElementById('rep-submenu');
            const chevron = document.getElementById('rep-chevron');
            menu.classList.toggle('hidden');
            chevron.classList.toggle('rotate-180');
        }

        function toggleSubj() {
            const menu = document.getElementById('subj-submenu');
            const chevron = document.getElementById('subj-chevron');
            menu.classList.toggle('hidden');
            chevron.classList.toggle('rotate-180');
        }
    </script>

    <script>
        (function() {
            const sidebar = document.getElementById('admin-sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const toggle = document.getElementById('sidebar-toggle');
            if (!sidebar || !toggle) return;

            // Mobile: start hidden; Desktop: start visible
            if (window.innerWidth < 1024) {
                sidebar.classList.add('sidebar-closed');
            }

            toggle.addEventListener('click', function() {
                sidebar.classList.toggle('sidebar-closed');
                // Overlay only needed on mobile (desktop uses width collapse)
                if (overlay) {
                    const isOpen = !sidebar.classList.contains('sidebar-closed');
                    if (window.innerWidth < 1024 && isOpen) {
                        overlay.classList.remove('hidden');
                    } else {
                        overlay.classList.add('hidden');
                    }
                }
            });

            if (overlay) {
                overlay.addEventListener('click', function() {
                    sidebar.classList.add('sidebar-closed');
                    overlay.classList.add('hidden');
                });
            }

            // When resizing to desktop, reset sidebar to visible
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
