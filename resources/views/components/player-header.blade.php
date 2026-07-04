@props(['player', 'ranking' => null, 'rankingMovement' => null])

<div class="flex items-center gap-4">
    <span class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $player->full_name }}</span>

    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 border border-gray-200 dark:bg-white/10 dark:text-white/70 dark:border-white/10">
        {{ $player->country_code }}
    </span>

    @if($player->world_ranking)
        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200 dark:bg-amber-500/20 dark:text-amber-400 dark:border-amber-500/30">
            #{{ $player->world_ranking }}
            @if($rankingMovement !== null && $rankingMovement !== 0)
                <span class="ml-0.5 {{ $rankingMovement > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500 dark:text-red-400' }}">
                    {{ $rankingMovement > 0 ? '↑' . $rankingMovement : '↓' . abs($rankingMovement) }}
                </span>
            @endif
        </span>
    @endif

    @if($player->rating_points)
        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 border border-blue-200 dark:bg-blue-500/20 dark:text-blue-400 dark:border-blue-500/30">
            {{ number_format($player->rating_points) }} pts
        </span>
    @endif

    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-50 text-gray-500 border border-gray-200 dark:bg-white/5 dark:text-white/50 dark:border-white/10">
        {{ $player->dominant_hand }}
    </span>
</div>
