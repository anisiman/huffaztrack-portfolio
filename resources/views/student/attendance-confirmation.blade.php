<!DOCTYPE html>
<html>
<head>
    <title>Attendance Confirmation</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-6">
    <div class="w-full max-w-md rounded-3xl bg-white p-8 shadow-xl shadow-slate-200/70">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-green-100 text-green-600">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-7 w-7">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
            </svg>
        </div>

        <h1 class="mt-5 text-center text-4xl font-bold text-black">Attendance Confirmation</h1>

        @if (session('success'))
            <p class="mt-4 rounded-2xl bg-green-50 px-4 py-3 text-center text-sm font-medium text-green-700">
                {{ session('success') }}
            </p>
        @elseif (session('error'))
            <p class="mt-4 rounded-2xl bg-red-50 px-4 py-3 text-center text-sm font-medium text-red-700">
                {{ session('error') }}
            </p>
        @elseif (session('info'))
            <p class="mt-4 rounded-2xl bg-blue-50 px-4 py-3 text-center text-sm font-medium text-blue-700">
                {{ session('info') }}
            </p>
        @else
            <p class="mt-4 rounded-2xl bg-slate-50 px-4 py-3 text-center text-sm font-medium text-slate-600">
                Your attendance status has been processed for this session.
            </p>
        @endif

        <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
            <strong class="text-slate-800">Session ID:</strong> {{ $sessionId }}
        </div>

        <p class="mt-5 text-center text-sm text-slate-500">
            You can close this page now.
        </p>
    </div>
</body>
</html>
