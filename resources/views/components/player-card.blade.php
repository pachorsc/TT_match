@props(['player', 'stats' => null])

<div class="rounded-2xl bg-gray-50 border border-gray-200 dark:bg-white/[0.03] dark:border-white/[0.06] p-6 space-y-4 transition-colors duration-300">
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-bold tracking-tight text-gray-900 dark:text-white">{{ $player->full_name }}</h3>
        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 border border-gray-200 dark:bg-white/10 dark:text-white/70 dark:border-white/10">
            {{ $player->country_code }}
        </span>
    </div>

    <div class="grid grid-cols-2 gap-3 text-sm">
        <div class="space-y-1">
            <span class="text-gray-400 dark:text-white/40 text-xs uppercase tracking-wider">Ranking</span>
            <p class="font-bold text-lg text-gray-900 dark:text-white">{{ $player->world_ranking ? '#' . $player->world_ranking : '—' }}</p>
        </div>

        <div class="space-y-1">
            <span class="text-gray-400 dark:text-white/40 text-xs uppercase tracking-wider">Rating</span>
            <p class="font-bold text-lg text-gray-900 dark:text-white">{{ $player->rating_points ? number_format($player->rating_points) : '—' }}</p>
        </div>

        <div class="space-y-1">
            <span class="text-gray-400 dark:text-white/40 text-xs uppercase tracking-wider">Hand</span>
            <p class="font-semibold text-gray-700 dark:text-white/80">{{ $player->dominant_hand }}</p>
        </div>

        <div class="space-y-1">
            <span class="text-gray-400 dark:text-white/40 text-xs uppercase tracking-wider">Style</span>
            <p class="font-semibold text-gray-700 dark:text-white/80">{{ $player->playing_style ?? '—' }}</p>
        </div>

        <div class="space-y-1">
            <span class="text-gray-400 dark:text-white/40 text-xs uppercase tracking-wider">Age</span>
            <p class="font-semibold text-gray-700 dark:text-white/80">{{ $player->date_of_birth->age }} yrs</p>
        </div>

        <div class="space-y-1">
            <span class="text-gray-400 dark:text-white/40 text-xs uppercase tracking-wider">Height</span>
            <p class="font-semibold text-gray-700 dark:text-white/80">{{ $player->height_cm ? $player->height_cm . ' cm' : '—' }}</p>
        </div>
    </div>

    @if($stats)
        <div class="pt-3 border-t border-gray-200 dark:border-white/[0.06]">
            <div class="grid grid-cols-4 gap-2 text-center">
                <div class="space-y-0.5">
                    <span class="text-gray-400 dark:text-white/40 text-xs uppercase tracking-wider">Matches</span>
                    <p class="font-bold text-gray-900 dark:text-white">{{ $stats['total_matches'] }}</p>
                </div>
                <div class="space-y-0.5">
                    <span class="text-gray-400 dark:text-white/40 text-xs uppercase tracking-wider">Wins</span>
                    <p class="font-bold text-emerald-600 dark:text-emerald-400">{{ $stats['wins'] }}</p>
                </div>
                <div class="space-y-0.5">
                    <span class="text-gray-400 dark:text-white/40 text-xs uppercase tracking-wider">Losses</span>
                    <p class="font-bold text-red-500 dark:text-red-400">{{ $stats['losses'] }}</p>
                </div>
                <div class="space-y-0.5">
                    <span class="text-gray-400 dark:text-white/40 text-xs uppercase tracking-wider">Win %</span>
                    <p class="font-bold text-gray-900 dark:text-white">{{ $stats['win_rate'] }}%</p>
                </div>
            </div>
        </div>
    @endif
</div>
