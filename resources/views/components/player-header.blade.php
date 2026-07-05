@props(['player', 'ranking' => null, 'rankingMovement' => null])

<div class="flex flex-col sm:flex-row items-center sm:items-start gap-3 sm:gap-4">
    <span class="text-2xl sm:text-3xl font-bold tracking-tight text-white/90">{{ $player->full_name }}</span>

    <div class="flex flex-wrap justify-center sm:justify-start items-center gap-2">
        <span class="badge-glass">
            {{ $player->country_code }}
        </span>

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
            <span class="badge-blue-glass">
                {{ number_format($player->rating_points) }} pts
            </span>
        @endif

        <span class="badge-glass">
            {{ $player->dominant_hand }}
        </span>
    </div>
</div>
