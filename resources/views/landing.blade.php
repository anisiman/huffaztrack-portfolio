<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HuffazTrack – Huffaz Education Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #0f0b2e;
            color: #fff;
            overflow-x: hidden;
        }

        /* Smooth scroll */
        html { scroll-behavior: smooth; }

        /* ── Navbar ── */
        .navbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 100;
            background: rgba(15, 11, 46, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255,255,255,0.07);
            padding: 14px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .nav-logo-icon {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, #7c3aed, #5b21b6);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .nav-logo-text {
            font-size: 18px;
            font-weight: 700;
            color: #fff;
        }

        .nav-login-btn {
            background: linear-gradient(135deg, #7c3aed, #5b21b6);
            color: #fff;
            padding: 8px 22px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: opacity 0.2s, transform 0.2s;
        }

        .nav-login-btn:hover {
            opacity: 0.85;
            transform: translateY(-1px);
        }

        /* ── Hero Section ── */
        .hero {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 120px 20px 80px;
            position: relative;
            overflow: hidden;
        }

        /* Background glow blobs */
        .hero-glow-left {
            position: absolute;
            top: 30%; left: -10%;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(124,58,237,0.28) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero-glow-right {
            position: absolute;
            top: 20%; right: -10%;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(109,40,217,0.22) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero-badge {
            display: inline-block;
            background: rgba(124,58,237,0.18);
            border: 1px solid rgba(124,58,237,0.35);
            color: #a78bfa;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            padding: 6px 18px;
            border-radius: 100px;
            margin-bottom: 24px;
        }

        .hero-title {
            font-size: clamp(40px, 7vw, 76px);
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 20px;
            letter-spacing: -1px;
        }

        .hero-title span {
            background: linear-gradient(135deg, #a78bfa, #7c3aed, #c4b5fd);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-tagline {
            font-size: clamp(16px, 2.5vw, 20px);
            color: rgba(255,255,255,0.60);
            max-width: 580px;
            margin: 0 auto 40px;
            line-height: 1.65;
        }

        .hero-arabic {
            font-size: 14px;
            color: rgba(167,139,250,0.70);
            letter-spacing: 1.5px;
            margin-bottom: 44px;
        }

        .hero-cta {
            display: inline-block;
            background: linear-gradient(135deg, #7c3aed, #5b21b6);
            color: #fff;
            padding: 14px 40px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 12px 36px rgba(124,58,237,0.45);
            transition: opacity 0.2s, transform 0.2s, box-shadow 0.2s;
        }

        .hero-cta:hover {
            opacity: 0.88;
            transform: translateY(-2px);
            box-shadow: 0 18px 48px rgba(124,58,237,0.60);
        }

        /* Decorative rings under hero */
        .hero-rings {
            position: absolute;
            bottom: -60px; left: 50%;
            transform: translateX(-50%);
            width: 600px; height: 300px;
            border-radius: 50%;
            border: 1px solid rgba(124,58,237,0.12);
            pointer-events: none;
        }

        /* ── Divider ── */
        .section-divider {
            border: none;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(124,58,237,0.30), transparent);
            margin: 0;
        }

        /* ── Sections General ── */
        .section {
            padding: 90px 20px;
            max-width: 1100px;
            margin: 0 auto;
        }

        .section-label {
            text-align: center;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #a78bfa;
            margin-bottom: 12px;
        }

        .section-title {
            text-align: center;
            font-size: clamp(26px, 4vw, 40px);
            font-weight: 800;
            margin-bottom: 14px;
            letter-spacing: -0.5px;
        }

        .section-subtitle {
            text-align: center;
            color: rgba(255,255,255,0.52);
            font-size: 16px;
            max-width: 520px;
            margin: 0 auto 56px;
            line-height: 1.65;
        }

        /* ── Features Grid ── */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 20px;
        }

        .feature-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px;
            padding: 28px 24px;
            transition: background 0.2s, border-color 0.2s, transform 0.2s;
        }

        .feature-card:hover {
            background: rgba(124,58,237,0.10);
            border-color: rgba(124,58,237,0.30);
            transform: translateY(-3px);
        }

        .feature-icon {
            width: 48px; height: 48px;
            background: linear-gradient(135deg, #7c3aed, #5b21b6);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 18px;
        }

        .feature-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .feature-desc {
            font-size: 13.5px;
            color: rgba(255,255,255,0.52);
            line-height: 1.6;
        }

        /* ── Roles Section ── */
        .roles-section {
            background: rgba(255,255,255,0.02);
            border-top: 1px solid rgba(255,255,255,0.06);
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }

        .roles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
        }

        .role-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px;
            padding: 32px 26px;
            text-align: center;
            transition: background 0.2s, border-color 0.2s, transform 0.2s;
        }

        .role-card:hover {
            background: rgba(124,58,237,0.10);
            border-color: rgba(124,58,237,0.30);
            transform: translateY(-3px);
        }

        .role-avatar {
            width: 64px; height: 64px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
            font-size: 26px;
        }

        .role-name {
            font-size: 18px;
            font-weight: 800;
            margin-bottom: 6px;
        }

        .role-badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 3px 12px;
            border-radius: 100px;
            margin-bottom: 16px;
        }

        .role-desc {
            font-size: 13.5px;
            color: rgba(255,255,255,0.52);
            line-height: 1.6;
        }

        .role-list {
            list-style: none;
            padding: 0;
            margin: 14px 0 0;
            text-align: left;
        }

        .role-list li {
            font-size: 13px;
            color: rgba(255,255,255,0.60);
            padding: 5px 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .role-list li:last-child { border-bottom: none; }

        .role-list li::before {
            content: '';
            width: 6px; height: 6px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        /* ── Footer ── */
        .footer {
            background: rgba(0,0,0,0.30);
            border-top: 1px solid rgba(255,255,255,0.07);
            padding: 40px 20px;
            text-align: center;
        }

        .footer-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .footer-name {
            font-size: 18px;
            font-weight: 700;
        }

        .footer-copy {
            font-size: 13px;
            color: rgba(255,255,255,0.35);
        }

        /* ── Responsive ── */
        @media (max-width: 640px) {
            .navbar { padding: 12px 20px; }
            .hero { padding: 100px 16px 70px; }
            .section { padding: 70px 16px; }
            .hero-rings { display: none; }
        }
    </style>
</head>
<body>

{{-- ═══════════════════════════════════════
     NAVBAR
════════════════════════════════════════ --}}
<nav class="navbar">
    <a href="#" class="nav-logo">
        <div class="nav-logo-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <span class="nav-logo-text">HuffazTrack</span>
    </a>
    <a href="{{ route('login') }}" class="nav-login-btn">Sign In</a>
</nav>


{{-- ═══════════════════════════════════════
     HERO SECTION
════════════════════════════════════════ --}}
<section class="hero">
    <div class="hero-glow-left"></div>
    <div class="hero-glow-right"></div>

    <div class="hero-badge">Integrated Education Management System</div>

    <h1 class="hero-title">
        A Digital Management<br>
        <span>Platform for HuffazPro</span>
    </h1>

    <p class="hero-tagline">
        HuffazTrack streamlines the day-to-day operations of huffaz institutions — managing
        student records, attendance, hafazan progress, academic performance, and
        reporting in one centralised system.
    </p>

    <p class="hero-arabic">وَرَتِّلِ الْقُرْآنَ تَرْتِيلًا</p>

    <a href="{{ route('login') }}" class="hero-cta">Get Started →</a>

    <div class="hero-rings"></div>
</section>

<hr class="section-divider">


{{-- ═══════════════════════════════════════
     FEATURES SECTION
════════════════════════════════════════ --}}
<div class="section">
    <p class="section-label">Platform Capabilities</p>
    <h2 class="section-title">Everything in One Platform</h2>
    <p class="section-subtitle">
        From daily attendance to academic reporting — a complete digital solution for managing huffaz student life.
    </p>

    <div class="features-grid">

        {{-- Feature 1: QR Attendance --}}
        <div class="feature-card">
            <div class="feature-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                    <rect x="3" y="3" width="7" height="7" rx="1" stroke="white" stroke-width="2"/>
                    <rect x="14" y="3" width="7" height="7" rx="1" stroke="white" stroke-width="2"/>
                    <rect x="3" y="14" width="7" height="7" rx="1" stroke="white" stroke-width="2"/>
                    <path d="M14 14h2v2h-2zM18 14h3v3h-3zM14 18h3v3h-3z" stroke="white" stroke-width="1.5"/>
                </svg>
            </div>
            <div class="feature-title">QR-Based Attendance</div>
            <p class="feature-desc">
                Replace manual registers with a digital QR system. Lecturers open a session; students scan and are marked present — fast, accurate, and paperless.
            </p>
        </div>

        {{-- Feature 2: Hafazan Tracking --}}
        <div class="feature-card">
            <div class="feature-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9 9l2 2 4-4" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="feature-title">Hafazan Progress Tracking</div>
            <p class="feature-desc">
                Log and monitor each student's memorisation progress through structured daily sessions and checkpoint evaluations, giving lecturers a clear view of each student's standing.
            </p>
        </div>

        {{-- Feature 3: Analytics --}}
        <div class="feature-card">
            <div class="feature-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                    <path d="M18 20V10M12 20V4M6 20v-6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="feature-title">Analytics & Reports</div>
            <p class="feature-desc">
                Interactive dashboards and exportable PDF reports give admins and lecturers a data-driven view of attendance trends, hafazan progress, and overall student performance.
            </p>
        </div>

        {{-- Feature 4: Student Records --}}
        <div class="feature-card">
            <div class="feature-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="8" r="4" stroke="white" stroke-width="2"/>
                    <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke="white" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
            <div class="feature-title">Student Records & Management</div>
            <p class="feature-desc">
                Centralise student biodata, batch groupings, programme enrolment, and academic results in one structured masterlist — always up to date and easy to navigate.
            </p>
        </div>

    </div>
</div>

<hr class="section-divider">


{{-- ═══════════════════════════════════════
     ROLES SECTION
════════════════════════════════════════ --}}
<div class="roles-section">
    <div class="section">
        <p class="section-label">User Roles</p>
        <h2 class="section-title">Built for Every Role</h2>
        <p class="section-subtitle">
            Three dedicated portals — each tailored to the responsibilities and access level of the user.
        </p>

        <div class="roles-grid">

            {{-- Admin --}}
            <div class="role-card">
                <div class="role-avatar" style="background: rgba(124,58,237,0.18);">🛡️</div>
                <div class="role-name">Admin</div>
                <span class="role-badge" style="background: rgba(124,58,237,0.20); color: #a78bfa;">System Administrator</span>
                <p class="role-desc">Full control over the platform — managing users, academic data, and institutional records.</p>
                <ul class="role-list">
                    <li><span style="width:6px;height:6px;border-radius:50%;background:#a78bfa;flex-shrink:0;display:inline-block;"></span> Manage student & lecturer accounts</li>
                    <li><span style="width:6px;height:6px;border-radius:50%;background:#a78bfa;flex-shrink:0;display:inline-block;"></span> Monitor academic performance & hafazan data</li>
                    <li><span style="width:6px;height:6px;border-radius:50%;background:#a78bfa;flex-shrink:0;display:inline-block;"></span> Access institution-wide analytics & reports</li>
                </ul>
            </div>

            {{-- Lecturer --}}
            <div class="role-card">
                <div class="role-avatar" style="background: rgba(16,163,127,0.18);">👨‍🏫</div>
                <div class="role-name">Lecturer</div>
                <span class="role-badge" style="background: rgba(16,163,127,0.20); color: #34d399;">Halaqah Lecturer</span>
                <p class="role-desc">Manage your group's attendance, hafazan sessions, and academic progress from one dashboard.</p>
                <ul class="role-list">
                    <li><span style="width:6px;height:6px;border-radius:50%;background:#34d399;flex-shrink:0;display:inline-block;"></span> Run QR attendance sessions for classes</li>
                    <li><span style="width:6px;height:6px;border-radius:50%;background:#34d399;flex-shrink:0;display:inline-block;"></span> Record and evaluate hafazan sessions</li>
                    <li><span style="width:6px;height:6px;border-radius:50%;background:#34d399;flex-shrink:0;display:inline-block;"></span> Track student progress & export reports</li>
                </ul>
            </div>

            {{-- Student --}}
            <div class="role-card">
                <div class="role-avatar" style="background: rgba(59,130,246,0.18);">🎓</div>
                <div class="role-name">Student</div>
                <span class="role-badge" style="background: rgba(59,130,246,0.20); color: #60a5fa;">Huffaz Student</span>
                <p class="role-desc">Stay informed on your own attendance, hafazan standing, and academic performance — all in one place.</p>
                <ul class="role-list">
                    <li><span style="width:6px;height:6px;border-radius:50%;background:#60a5fa;flex-shrink:0;display:inline-block;"></span> Scan QR code to record class attendance</li>
                    <li><span style="width:6px;height:6px;border-radius:50%;background:#60a5fa;flex-shrink:0;display:inline-block;"></span> View hafazan progress & academic results</li>
                    <li><span style="width:6px;height:6px;border-radius:50%;background:#60a5fa;flex-shrink:0;display:inline-block;"></span> Access personal profile & dashboard</li>
                </ul>
            </div>

        </div>
    </div>
</div>


{{-- ═══════════════════════════════════════
     FOOTER
════════════════════════════════════════ --}}
<footer class="footer">
    <div class="footer-logo">
        <div class="nav-logo-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <span class="footer-name">HuffazTrack</span>
    </div>
    <p class="footer-copy">
        &copy; {{ date('Y') }} HuffazTrack. Final Year Project — Huffaz Education Management System.
    </p>
</footer>

</body>
</html>
