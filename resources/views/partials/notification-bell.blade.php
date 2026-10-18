@php
    $notifItems = \App\Models\Notification::where('user_id', auth()->id())
        ->orderByDesc('created_at')
        ->limit(10)
        ->get();
    $notifUnreadCount = $notifItems->where('is_read', false)->count();
@endphp

<div class="relative">
    <button id="notif-btn"
            class="relative rounded-full p-2 text-slate-500 transition hover:bg-slate-100 hover:text-violet-600"
            aria-label="Notifications">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
             stroke-width="1.8" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/>
        </svg>
        @if($notifUnreadCount > 0)
            <span class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-[9px]
                         min-w-[16px] h-4 px-[3px] flex items-center justify-center
                         rounded-full font-bold leading-none pointer-events-none">
                {{ $notifUnreadCount > 9 ? '9+' : $notifUnreadCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown panel --}}
    <div id="notif-dropdown"
         class="absolute right-0 top-full mt-2 w-80 bg-white border border-slate-100
                rounded-2xl shadow-xl z-50 hidden overflow-hidden"
         style="min-width:320px;">

        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <h3 class="text-sm font-semibold text-slate-700">Notifications</h3>
                @if($notifUnreadCount > 0)
                    <span class="text-xs bg-violet-100 text-violet-700 font-semibold px-1.5 py-0.5 rounded-full">
                        {{ $notifUnreadCount }} new
                    </span>
                @endif
            </div>
            @if($notifUnreadCount > 0)
                <form action="{{ route('notifications.readAll') }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="text-xs text-violet-600 hover:text-violet-800 font-medium transition">
                        Mark all read
                    </button>
                </form>
            @endif
        </div>

        {{-- List --}}
        <div class="overflow-y-auto" style="max-height:320px;">
            @forelse($notifItems as $notif)
                <a href="{{ route('notifications.open', $notif->id) }}"
                   class="flex items-start gap-3 px-4 py-3 border-b border-slate-50 last:border-0
                          hover:bg-slate-50 transition {{ $notif->is_read ? '' : 'bg-violet-50/40' }}">

                    {{-- Type icon --}}
                    @switch($notif->type)
                        @case('attendance')
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-500 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4">
                                    <rect x="3" y="4" width="18" height="18" rx="2" stroke-linecap="round"/>
                                    <path d="M16 2v4M8 2v4M3 10h18" stroke-linecap="round"/>
                                    <path d="M9 16l2 2 4-4" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            @break
                        @case('hafazan')
                            <div class="w-8 h-8 rounded-full bg-violet-100 text-violet-500 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4">
                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            @break
                        @case('warning')
                            <div class="w-8 h-8 rounded-full bg-red-100 text-red-500 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4">
                                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" stroke-linecap="round" stroke-linejoin="round"/>
                                    <line x1="12" y1="9" x2="12" y2="13" stroke-linecap="round"/>
                                    <line x1="12" y1="17" x2="12.01" y2="17" stroke-linecap="round"/>
                                </svg>
                            </div>
                            @break
                        @case('account')
                            <div class="w-8 h-8 rounded-full bg-green-100 text-green-500 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="9" cy="7" r="4" stroke-linecap="round"/>
                                    <line x1="19" y1="8" x2="19" y2="14" stroke-linecap="round"/>
                                    <line x1="22" y1="11" x2="16" y2="11" stroke-linecap="round"/>
                                </svg>
                            </div>
                            @break
                        @case('note')
                            <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-500 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            @break
                        @case('report')
                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-500 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4">
                                    <path d="M3 3v18h18" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M7 16l4-4 4 4 4-5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            @break
                        @default
                            <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4">
                                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M13.73 21a2 2 0 0 1-3.46 0" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                    @endswitch

                    {{-- Text content --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm {{ $notif->is_read ? 'font-normal text-slate-700' : 'font-semibold text-slate-800' }} leading-snug truncate">
                            {{ $notif->title }}
                        </p>
                        <p class="text-xs text-slate-500 mt-0.5 leading-relaxed line-clamp-2">
                            {{ $notif->message }}
                        </p>
                        <p class="text-[10px] text-slate-400 mt-1">
                            {{ $notif->created_at->diffForHumans() }}
                        </p>
                    </div>

                    {{-- Unread dot --}}
                    @if(! $notif->is_read)
                        <div class="w-2 h-2 bg-violet-500 rounded-full flex-shrink-0 mt-2"></div>
                    @endif
                </a>
            @empty
                <div class="px-4 py-10 text-center">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                         class="w-8 h-8 text-slate-300 mx-auto mb-2">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <p class="text-sm text-slate-400">No notifications yet</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
(function () {
    var btn      = document.getElementById('notif-btn');
    var dropdown = document.getElementById('notif-dropdown');
    if (!btn || !dropdown) return;

    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        var profileDropdown = document.getElementById('profile-dropdown');
        if (profileDropdown) profileDropdown.classList.add('hidden');
        dropdown.classList.toggle('hidden');
    });

    document.addEventListener('click', function () {
        dropdown.classList.add('hidden');
    });
}());
</script>
