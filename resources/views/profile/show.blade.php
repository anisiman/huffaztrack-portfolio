{{--
    Shared profile page — works for Admin, Lecturer, and Student.
--}}

@php
    if (Auth::user()->role === 'admin') {
        $layout = 'layouts.admin';
        $roleLabel = 'Administrator';
    } elseif (Auth::user()->role === 'lecturer') {
        $layout = 'layouts.lecturer';
        $roleLabel = 'Lecturer';
    } else {
        $layout = 'layouts.student';
        $roleLabel = 'Student';
    }

    $avatarUrl = $user->profile_picture
        ? asset('storage/' . $user->profile_picture)
        : null;
@endphp

@extends($layout)

@section('title', 'My Profile')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- ── Page Title ── --}}
    @include('partials.back-button', ['href' => 'javascript:history.back()'])
    <div>
        <h1 class="text-2xl font-bold text-slate-800 leading-tight">My Profile</h1>
        <p class="text-sm text-slate-400 mt-0.5">Manage your account details and photo.</p>
    </div>

    {{-- ── Avatar Upload Card ── --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">

        {{-- Centered avatar + name --}}
        <div class="flex flex-col items-center text-center gap-3 mb-5">

            {{-- Avatar --}}
            <div class="relative group flex-shrink-0" id="avatar-wrapper">
                <div id="avatar-display"
                    class="w-32 h-32 rounded-full overflow-hidden flex items-center justify-center shadow-md"
                    style="background:{{ $avatarUrl ? '#fff' : '#ede9fe' }}; border: 4px solid #f1f5f9;">
                    @if($avatarUrl)
                        <img id="avatar-img" src="{{ $avatarUrl }}" alt="Profile" class="w-full h-full object-cover">
                    @else
                        <span id="avatar-initial" class="text-4xl font-bold" style="color:#7c3aed;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </span>
                    @endif
                </div>

                {{-- Camera overlay on hover --}}
                <label for="avatar-input"
                    class="absolute inset-0 rounded-full flex flex-col items-center justify-center cursor-pointer transition-all duration-200 opacity-0 group-hover:opacity-100"
                    style="background:rgba(0,0,0,0.42);">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.8" stroke="white" class="w-7 h-7">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                    </svg>
                    <span class="text-white text-[10px] font-semibold mt-1">Change photo</span>
                </label>
            </div>

            {{-- Name + role --}}
            <div>
                <p class="text-xl font-bold text-slate-800 leading-tight">{{ $user->name }}</p>
                <span class="inline-block mt-1.5 text-xs font-semibold px-3 py-1 rounded-full"
                    style="background:#ede9fe; color:#6d28d9;">{{ $roleLabel }}</span>
            </div>

            {{-- Hint or remove --}}
            @if($user->profile_picture)
                <form method="POST" action="{{ route('profile.removeAvatar') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="flex items-center gap-1.5 text-xs text-slate-400 hover:text-red-500 transition mt-1">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-3.5 h-3.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                        Remove photo
                    </button>
                </form>
            @else
                <p class="text-xs text-slate-400 mt-1">Hover photo and click to upload · Max 2 MB · JPG, PNG, WEBP</p>
            @endif
        </div>

        {{-- Flash message --}}
        @if(session('avatar_success'))
            <div class="flex items-center gap-2 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 flex-shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                {{ session('avatar_success') }}
            </div>
        @endif

        {{-- Upload preview panel (hidden until file selected) --}}
        <div id="upload-panel" class="hidden flex items-center gap-3 rounded-xl border border-violet-200 bg-violet-50 px-4 py-3">
            <img id="upload-preview" src="" alt="" class="w-10 h-10 rounded-full object-cover border-2 border-violet-300 flex-shrink-0">
            <div class="flex-1 min-w-0">
                <p id="upload-filename" class="text-sm font-medium text-slate-700 truncate"></p>
                <p class="text-xs text-slate-400">Ready to upload</p>
            </div>
            <div class="flex gap-2 flex-shrink-0">
                <button id="upload-confirm" type="button"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white transition"
                    style="background:#7c3aed;">Save</button>
                <button id="upload-cancel" type="button"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition">Cancel</button>
            </div>
        </div>

        {{-- Hidden upload form --}}
        <form id="avatar-form" method="POST" action="{{ route('profile.updateAvatar') }}" enctype="multipart/form-data" class="hidden">
            @csrf
            <input id="avatar-input" type="file" name="profile_picture" accept="image/jpeg,image/png,image/jpg,image/webp" class="hidden">
        </form>

    </div>

    {{-- ── Account Info Card ── --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <h2 class="text-base font-bold text-slate-800 mb-4">Account Details</h2>
        <div class="space-y-4">
            <div class="flex flex-col gap-1">
                <label class="text-xs font-semibold uppercase tracking-wider text-slate-400">Full Name</label>
                <p class="text-sm font-medium text-slate-700 bg-slate-50 rounded-lg px-4 py-3">{{ $user->name }}</p>
            </div>
            <div class="flex flex-col gap-1">
                <label class="text-xs font-semibold uppercase tracking-wider text-slate-400">Email</label>
                <p class="text-sm font-medium text-slate-700 bg-slate-50 rounded-lg px-4 py-3">{{ $user->email }}</p>
            </div>
            <div class="flex flex-col gap-1">
                <label class="text-xs font-semibold uppercase tracking-wider text-slate-400">Role</label>
                <p class="text-sm font-medium text-slate-700 bg-slate-50 rounded-lg px-4 py-3">{{ $roleLabel }}</p>
            </div>
        </div>
    </div>

    @if ($user->role === 'student')
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <p class="text-sm text-slate-700">
                Your full student biodata is now available on the <a href="{{ route('student.biodata') }}" class="font-semibold text-violet-700 hover:text-violet-900">Student Biodata</a> page.
            </p>
        </div>
    @endif

    {{-- ── Change Password Card ── --}}
    <div id="change-password" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <h2 class="text-base font-bold text-slate-800 mb-4">Change Password</h2>

        @if(session('success'))
            <div class="mb-4 flex items-center gap-2 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 flex-shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('profile.updatePassword') }}" class="space-y-4">
            @csrf
            <div class="flex flex-col gap-1">
                <label class="text-xs font-semibold uppercase tracking-wider text-slate-400">Current Password</label>
                <input type="password" name="current_password"
                    class="w-full rounded-lg border border-slate-200 px-4 py-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-violet-400 @error('current_password') border-red-400 @enderror"
                    placeholder="Enter current password">
                @error('current_password')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex flex-col gap-1">
                <label class="text-xs font-semibold uppercase tracking-wider text-slate-400">New Password</label>
                <input type="password" name="new_password"
                    class="w-full rounded-lg border border-slate-200 px-4 py-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-violet-400 @error('new_password') border-red-400 @enderror"
                    placeholder="Minimum 8 characters">
                @error('new_password')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex flex-col gap-1">
                <label class="text-xs font-semibold uppercase tracking-wider text-slate-400">Confirm New Password</label>
                <input type="password" name="new_password_confirmation"
                    class="w-full rounded-lg border border-slate-200 px-4 py-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-violet-400"
                    placeholder="Repeat new password">
            </div>
            <button type="submit"
                class="w-full rounded-lg px-4 py-3 text-sm font-semibold text-white transition"
                style="background:#7c3aed;" onmouseover="this.style.background='#6d28d9'" onmouseout="this.style.background='#7c3aed'">
                Update Password
            </button>
        </form>
    </div>

</div>

<script>
(function () {
    const input      = document.getElementById('avatar-input');
    const form       = document.getElementById('avatar-form');
    const panel      = document.getElementById('upload-panel');
    const preview    = document.getElementById('upload-preview');
    const filename   = document.getElementById('upload-filename');
    const confirmBtn = document.getElementById('upload-confirm');
    const cancelBtn  = document.getElementById('upload-cancel');
    const display    = document.getElementById('avatar-display');

    if (!input) return;

    input.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        // Live preview
        const reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
            // Also update the big circle preview
            display.style.background = '#fff';
            display.innerHTML = '<img src="' + e.target.result + '" class="w-full h-full object-cover">';
        };
        reader.readAsDataURL(file);

        filename.textContent = file.name;
        panel.classList.remove('hidden');
    });

    confirmBtn.addEventListener('click', function () {
        if (!input.files || !input.files.length) return;
        form.submit();
    });

    cancelBtn.addEventListener('click', function () {
        input.value = '';
        panel.classList.add('hidden');
        // Restore original avatar display
        location.reload();
    });
}());
</script>
@endsection
