{{--
    Shared sidebar logo header.
    Included in: layouts/admin.blade.php, layouts/lecturer.blade.php, layouts/student.blade.php
    To update the logo for all roles, edit only this file.
--}}
<div class="flex items-center gap-3 px-5 py-5 border-b border-slate-100 flex-shrink-0">
    <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
        style="background: linear-gradient(135deg,#7c3aed,#5b21b6); box-shadow:0 4px 12px rgba(124,58,237,0.35);">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" stroke="white" stroke-width="2.2"
                stroke-linecap="round" stroke-linejoin="round" />
            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" stroke="white"
                stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </div>
    <div>
        <p class="text-sm font-bold text-slate-800 leading-tight">HuffazTrack</p>
        <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">Management</p>
    </div>
</div>
