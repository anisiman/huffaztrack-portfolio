{{--
    Reusable back button.
    @include('partials.back-button', ['href' => route('some.route'), 'label' => 'Reports'])
    $label is optional — omit for icon-only mode.
--}}
<a href="{{ $href }}"
   class="inline-flex items-center gap-2 group w-fit"
   style="text-decoration:none;">
    <span class="flex items-center justify-center w-8 h-8 rounded-full border border-slate-200 bg-white text-slate-400 shadow-sm transition-all group-hover:border-violet-300 group-hover:bg-violet-50 group-hover:text-violet-600">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
             stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
        </svg>
    </span>
    @if(!empty($label))
        <span class="text-sm font-semibold text-slate-500 transition-colors group-hover:text-violet-600">{{ $label }}</span>
    @endif
</a>
