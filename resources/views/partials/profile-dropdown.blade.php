{{--
    Shared profile dropdown menu.
    Usage: @include('partials.profile-dropdown')

    Requirements in the parent layout:
      - A button with  id="profile-btn"  wrapping the avatar + name
      - That button must be inside a  div class="relative"
      - This file is included INSIDE that relative div
--}}

<div id="profile-dropdown"
    class="absolute right-0 top-full mt-2 w-52 rounded-xl border border-slate-100 bg-white shadow-lg z-50 hidden">

    {{-- My Profile --}}
    <a href="{{ route('profile.show') }}"
        class="flex items-center gap-3 px-4 py-3 text-sm text-slate-700 hover:bg-violet-50 hover:text-violet-700 rounded-t-xl transition">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 flex-shrink-0">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
        </svg>
        My Profile
    </a>

    @if(Auth::user()->role === 'student')
        <a href="{{ route('student.biodata') }}"
            class="flex items-center gap-3 px-4 py-3 text-sm text-slate-700 hover:bg-violet-50 hover:text-violet-700 transition">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            My Biodata
        </a>
    @endif

    {{-- Change Password (scrolls to the form on the profile page) --}}
    <a href="{{ route('profile.show') }}#change-password"
        class="flex items-center gap-3 px-4 py-3 text-sm text-slate-700 hover:bg-violet-50 hover:text-violet-700 transition">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 flex-shrink-0">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
        </svg>
        Change Password
    </a>

    <div class="border-t border-slate-100">
        {{-- Logout --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="flex w-full items-center gap-3 px-4 py-3 text-sm text-red-500 hover:bg-red-50 hover:text-red-600 rounded-b-xl transition">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 flex-shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25" />
                </svg>
                Logout
            </button>
        </form>
    </div>
</div>

<script>
(function () {
    const btn      = document.getElementById('profile-btn');
    const dropdown = document.getElementById('profile-dropdown');
    if (!btn || !dropdown) return;

    // Toggle dropdown when profile button is clicked
    btn.addEventListener('click', function (e) {
        e.stopPropagation(); // prevent the document listener from closing it immediately
        dropdown.classList.toggle('hidden');
    });

    // Close dropdown when clicking anywhere else on the page
    document.addEventListener('click', function () {
        dropdown.classList.add('hidden');
    });
})();
</script>
