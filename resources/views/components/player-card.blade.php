@props(['player', 'stats' => null])

<div class="card-glass p-5 sm:p-6 space-y-5">
    <div class="flex items-center justify-between">
        <h3 class="text-base sm:text-lg font-bold tracking-tight text-white/90">{{ $player->full_name }}</h3>
        <span class="badge-glass">
            {{ $player->country_code }}
        </span>
    </div>

    <div class="grid grid-cols-2 gap-4 sm:gap-5">
        <div class="space-y-1">
            <span class="stat-label">Ranking</span>
            <p class="stat-value text-lg sm:text-xl">{{ $player->world_ranking ? '#' . $player->world_ranking : '—' }}</p>
        </div>

        <div class="space-y-1">
            <span class="stat-label">Rating</span>
            <p class="stat-value text-lg sm:text-xl">{{ $player->rating_points ? number_format($player->rating_points) : '—' }}</p>
        </div>

        <div class="space-y-1">
            <span class="stat-label">Hand</span>
            <p class="font-semibold text-white/70">{{ $player->dominant_hand }}</p>
        </div>

        <div class="space-y-1">
            <span class="stat-label">Style</span>
            <p class="font-semibold text-white/70">{{ $player->playing_style ?? '—' }}</p>
        </div>

        <div class="space-y-1">
            <span class="stat-label">Age</span>
            <p class="font-semibold text-white/70">{{ $player->date_of_birth->age }} yrs</p>
        </div>

        <div class="space-y-1">
            <span class="stat-label">Height</span>
            <p class="font-semibold text-white/70">{{ $player->height_cm ? $player->height_cm . ' cm' : '—' }}</p>
        </div>
    </div>

    @if($stats)
        <div class="pt-4 border-t border-white/[0.06]">
            <div class="grid grid-cols-4 gap-3 text-center">
                <div>
                    <p class="stat-value text-lg">{{ $stats['total_matches'] }}</p>
                    <span class="stat-label mt-0.5 block">Matches</span>
                </div>
                <div>
                    <p class="font-bold text-lg text-emerald-400">{{ $stats['wins'] }}</p>
                    <span class="stat-label mt-0.5 block">Wins</span>
                </div>
                <div>
                    <p class="font-bold text-lg text-red-400">{{ $stats['losses'] }}</p>
                    <span class="stat-label mt-0.5 block">Losses</span>
                </div>
                <div>
                    <p class="stat-value text-lg">{{ $stats['win_rate'] }}%</p>
                    <span class="stat-label mt-0.5 block">Win %</span>
                </div>
            </div>
        </div>
    @endif
</div>
