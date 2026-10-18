{{-- resources/views/admin/students/partials/info-block.blade.php --}}
<div>
    <label class="block text-[10px] font-black text-gray-400 uppercase mb-1.5 tracking-tighter">{{ $label }}</label>
    <p class="{{ isset($highlight) && $highlight ? 'text-purple-700 font-black' : 'text-gray-800 font-bold' }} text-[15px]">
        {{ $value }}
    </p>
</div>