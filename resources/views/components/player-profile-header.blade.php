@props(['player', 'rankingMovement' => null])

<div class="card-glass p-6 sm:p-8">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 sm:gap-6">
        <div class="space-y-2">
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">{{ $player->full_name }}</h1>
                <span class="badge-glass">{{ $player->country_code }}</span>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if($player->world_ranking)
                    <span class="badge-amber-glass">
                        #{{ $player->world_ranking }}
                        @if($rankingMovement !== null && $rankingMovement !== 0)
                            <span class="ml-0.5 {{ $rankingMovement > 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                {{ $rankingMovement > 0 ? '↑' . $rankingMovement : '↓' . abs($rankingMovement) }}
                            </span>
                        @endif
                    </span>
                @endif

                @if($player->rating_points)
                    <span class="badge-blue-glass">{{ number_format($player->rating_points) }} pts</span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-x-6 gap-y-3 text-sm">
            <div>
                <span class="stat-label">Hand</span>
                <p class="font-semibold text-white/80 mt-0.5">{{ $player->dominant_hand }}</p>
            </div>
            <div>
                <span class="stat-label">Age</span>
                <p class="font-semibold text-white/80 mt-0.5">{{ $player->date_of_birth ? $player->date_of_birth->age . ' yrs' : '—' }}</p>
            </div>
            <div>
                <span class="stat-label">Height</span>
                <p class="font-semibold text-white/80 mt-0.5">{{ $player->height_cm ? $player->height_cm . ' cm' : '—' }}</p>
            </div>
            <div>
                <span class="stat-label">Style</span>
                <p class="font-semibold text-white/80 mt-0.5">{{ $player->playing_style ?? '—' }}</p>
            </div>
        </div>
    </div>
</div>
