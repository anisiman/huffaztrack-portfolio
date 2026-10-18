<!DOCTYPE html>
<html>
<head>
    <title>Student Attendance Scan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/auth-pages.css') }}">
</head>
<body class="auth-shell">

    <div class="auth-card auth-card--compact">
        <h2 class="auth-card__title">Attendance Scan</h2>
        <p class="text-sm text-slate-500 mb-4">Please sign in with your HuffazTrack student account to record attendance for this QR session.</p>

        @if(session('error'))
            <div class="auth-alert auth-alert--error auth-alert--compact">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="auth-alert auth-alert--error auth-alert--compact">
                {{ $errors->first() }}
            </div>
        @endif

        <a href="{{ route('login') }}" class="auth-form__submit" style="display:block; text-align:center; text-decoration:none;">
            Sign In
        </a>
    </div>

</body>
</html>
