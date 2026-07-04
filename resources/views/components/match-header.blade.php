@props(['tournament', 'match'])

<div class="rounded-2xl bg-gray-50 border border-gray-200 dark:bg-white/[0.03] dark:border-white/[0.06] px-6 py-5 transition-colors duration-300">
    <div class="text-center space-y-2">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/40">{{ $tournament->name }}</p>

        <div class="flex items-center justify-center gap-3 text-sm text-gray-500 dark:text-white/50">
            <span>{{ $tournament->location }}, {{ $tournament->country }}</span>
            @if($tournament->category)
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-white/50">
                    {{ $tournament->category }}
                </span>
            @endif
        </div>

        <div class="flex items-center justify-center gap-4 text-sm">
            <span class="text-gray-600 dark:text-white/60">{{ $match->match_date->format('d M Y') }}</span>
            @if($match->match_time)
                <span class="text-gray-400 dark:text-white/40">{{ $match->match_time }}</span>
            @endif
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-white/50">
                {{ $match->round }}
            </span>
        </div>
    </div>
</div>
