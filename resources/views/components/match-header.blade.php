@props(['tournament', 'match'])

<div class="card-glass px-5 sm:px-6 py-4 sm:py-5">
    <div class="text-center space-y-3">
        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-gray-500 dark:text-white/40">{{ $tournament->name }}</p>

        <div class="flex flex-wrap items-center justify-center gap-2 text-sm text-gray-500 dark:text-white/50">
            <span>{{ $tournament->location }}, {{ $tournament->country }}</span>
            @if($tournament->category)
                <span class="badge-glass text-[10px] px-2 py-0.5">
                    {{ $tournament->category }}
                </span>
            @endif
        </div>

        <div class="flex flex-wrap items-center justify-center gap-3 text-sm">
            <span class="text-gray-600 dark:text-white/60">{{ $match->match_date->month === 1 && $match->match_date->day === 1 ? $match->match_date->format('Y') : $match->match_date->format('d M Y') }}</span>
            @if($match->match_time)
<span class="w-1 h-1 rounded-full bg-gray-300 dark:bg-white/20"></span>
                <span class="text-gray-500 dark:text-white/40">{{ $match->match_time }}</span>
            @endif
            <span class="w-1 h-1 rounded-full bg-gray-300 dark:bg-white/20"></span>
            <span class="badge-glass text-[11px] px-2.5 py-0.5 font-semibold">
                {{ $match->round }}
            </span>
        </div>
    </div>
</div>
