@props(['won' => null, 'label' => null])

@if($label)
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
        {{ $won ? 'bg-emerald-100 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/20 dark:text-emerald-400 dark:border-emerald-500/30' : 'bg-red-100 text-red-700 border border-red-200 dark:bg-red-500/20 dark:text-red-400 dark:border-red-500/30' }}">
        {{ $label }}
    </span>
@elseif($won === true)
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/20 dark:text-emerald-400 dark:border-emerald-500/30">
        W
    </span>
@elseif($won === false)
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-200 dark:bg-red-500/20 dark:text-red-400 dark:border-red-500/30">
        L
    </span>
@else
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-400 border border-gray-200 dark:bg-white/10 dark:text-white/40 dark:border-white/10">
        —
    </span>
@endif
