@props(['player', 'stats' => null, 'compact' => false, 'streak' => null])

@if($compact)
    <div class="card-glass p-4 sm:p-5">
        <div class="flex items-center gap-4">
            {{-- Win Rate Ring --}}
            @if($stats)
                @php
                    $winRate = $stats['win_rate'] ?? 0;
                    $radius = 28;
                    $circumference = 2 * M_PI * $radius;
                    $offset = $circumference - ($winRate / 100) * $circumference;
                    $ringColor = $winRate >= 60 ? 'rgba(16, 185, 129, 0.9)' : ($winRate >= 45 ? 'rgba(245, 158, 11, 0.8)' : 'rgba(239, 68, 68, 0.8)');
                @endphp
                <div class="wr-ring shrink-0">
                    <svg width="68" height="68" viewBox="0 0 68 68">
                        <circle class="wr-ring-track" cx="34" cy="34" r="{{ $radius }}" stroke-width="5"/>
                        <circle class="wr-ring-fill" cx="34" cy="34" r="{{ $radius }}" stroke-width="5"
                                stroke="{{ $ringColor }}"
                                stroke-dasharray="{{ $circumference }}"
                                stroke-dashoffset="{{ $offset }}"/>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-sm font-black text-white/90">{{ $winRate }}%</span>
                    </div>
                </div>
            @endif

            {{-- Player Info --}}
            <div class="flex-1 min-w-0 space-y-1">
                <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ route('players.show', $player) }}" class="text-sm sm:text-base font-bold tracking-tight text-white/90 hover:text-sport-400 transition-colors truncate">{{ $player->full_name }}</a>
                    <span class="badge-glass text-[10px] px-2 py-0.5 shrink-0">{{ $player->country_code }}</span>
                    @if($player->world_ranking)
                        <span class="badge-amber-glass text-[10px] px-2 py-0.5 shrink-0">#{{ $player->world_ranking }}</span>
                    @endif
                </div>

                @if($stats)
                    <div class="flex items-center gap-4 text-[11px]">
                        <span class="text-white/30">{{ $stats['total_matches'] }} partidos</span>
                        <span class="text-emerald-400/80 font-semibold">{{ $stats['wins'] }}W</span>
                        <span class="text-red-400/80 font-semibold">{{ $stats['losses'] }}L</span>
                        @if($player->rating_points)
                            <span class="text-white/25 hidden sm:inline">{{ number_format($player->rating_points) }} pts</span>
                        @endif
                    </div>
                @endif

                @if($streak && $streak['count'] > 0)
                    <div>
                        <x-streak-badge :streak="$streak" />
                    </div>
                @endif
            </div>
        </div>
    </div>
@else
    <div class="card-glass p-5 sm:p-6 space-y-5">
        <div class="flex items-center justify-between">
            <a href="{{ route('players.show', $player) }}" class="text-base sm:text-lg font-bold tracking-tight text-white/90 hover:text-sport-400 transition-colors">{{ $player->full_name }}</a>
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
                <p class="font-semibold text-white/70">{{ $player->date_of_birth ? $player->date_of_birth->age . ' yrs' : '—' }}</p>
            </div>

            <div class="space-y-1">
                <span class="stat-label">Height</span>
                <p class="font-semibold text-white/70">{{ $player->height_cm ? $player->height_cm . ' cm' : '—' }}</p>
            </div>
        </div>

        @if($stats)
            <div class="pt-4 border-t border-white/[0.06]">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
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
@endif
