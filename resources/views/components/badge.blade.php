@props(['won' => null, 'label' => null])

@if($label)
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border
        {{ $won ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' : 'bg-red-500/20 text-red-400 border-red-500/30' }}">
        {{ $label }}
    </span>
@elseif($won === true)
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
        W
    </span>
@elseif($won === false)
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-500/20 text-red-400 border border-red-500/30">
        L
    </span>
@else
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-black/5 dark:bg-white/10 text-gray-400 dark:text-white/30 border border-black/10 dark:border-white/10">
        —
    </span>
@endif
