<x-layout title="Estadísticas Avanzadas — Comparar Jugadores">

    <div class="space-y-10 sm:space-y-12">

        {{-- Header --}}
        <div class="text-center space-y-2">
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white">Estadísticas Avanzadas</h1>
            <p class="text-sm sm:text-base text-white/40">Comparación detallada entre dos jugadores</p>
        </div>

        {{-- Gender Toggle + Player Selectors --}}
        <div class="card-glass p-5 sm:p-8">

            {{-- Gender Tabs --}}
            <div class="flex items-center justify-center gap-2 mb-6 sm:mb-8">
                <a href="{{ route('compare.stats', ['gender' => 'M', 'player_a' => request('player_a'), 'player_b' => request('player_b')]) }}"
                   class="px-5 py-2 rounded-xl text-sm font-bold transition-all duration-200
                   {{ $gender === 'M' ? 'bg-sport-500/20 text-sport-400 border border-sport-500/30' : 'bg-white/[0.04] text-white/50 hover:text-white/70 hover:bg-white/[0.06] border border-transparent' }}>
                    ♂ Masculino
                </a>
                <a href="{{ route('compare.stats', ['gender' => 'F', 'player_a' => request('player_a'), 'player_b' => request('player_b')]) }}"
                   class="px-5 py-2 rounded-xl text-sm font-bold transition-all duration-200
                   {{ $gender === 'F' ? 'bg-sport-500/20 text-sport-400 border border-sport-500/30' : 'bg-white/[0.04] text-white/50 hover:text-white/70 hover:bg-white/[0.06] border border-transparent' }}">
                    ♀ Femenino
                </a>
            </div>

            {{-- Player Selectors --}}
            <form method="GET" action="{{ route('compare.stats') }}" class="space-y-6">
                <input type="hidden" name="gender" value="{{ $gender }}">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    {{-- Player A --}}
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-[0.1em] text-white/30 mb-2">Jugador A</label>
                        <div class="player-search relative">
                            <input type="hidden" name="player_a" class="player-search-hidden" value="{{ $playerA?->id }}">
                            <div class="relative">
                                <input type="text"
                                       class="player-search-input w-full bg-white/[0.04] border border-white/[0.08] rounded-xl px-4 py-3 pr-10 text-sm outline-none transition-all duration-200 placeholder:text-white/20 focus:border-sport-500/40 focus:bg-white/[0.06]"
                                       placeholder="Buscar jugador..."
                                       autocomplete="off"
                                       value="{{ $playerA?->full_name ?? '' }}"
                                       data-name="player_a">
                                <button type="button" class="player-search-clear absolute right-3 top-1/2 -translate-y-1/2 text-white/20 hover:text-white/60 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <div class="player-search-dropdown absolute top-full left-0 right-0 mt-1 z-50 max-h-60 overflow-y-auto rounded-xl bg-[#0f0f0f] border border-white/[0.08] shadow-2xl hidden">
                                @foreach($players as $p)
                                    <button type="button"
                                            class="player-search-item w-full text-left px-4 py-2.5 text-sm text-white/60 hover:bg-white/[0.06] transition-colors flex items-center justify-between gap-3"
                                            data-value="{{ $p->id }}"
                                            data-label="{{ $p->full_name }}"
                                            data-search="{{ strtolower($p->full_name . ' ' . $p->country_code) }}">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <span class="font-medium truncate">{{ $p->full_name }}</span>
                                            <span class="text-xs text-white/30 shrink-0">{{ $p->country_code }}</span>
                                        </div>
                                        <div class="flex items-center gap-3 shrink-0 text-xs">
                                            @if($p->world_ranking)
                                                <span class="text-amber-400/80 font-semibold">#{{ $p->world_ranking }}</span>
                                            @endif
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Player B --}}
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-[0.1em] text-white/30 mb-2">Jugador B</label>
                        <div class="player-search relative">
                            <input type="hidden" name="player_b" class="player-search-hidden" value="{{ $playerB?->id }}">
                            <div class="relative">
                                <input type="text"
                                       class="player-search-input w-full bg-white/[0.04] border border-white/[0.08] rounded-xl px-4 py-3 pr-10 text-sm outline-none transition-all duration-200 placeholder:text-white/20 focus:border-sport-500/40 focus:bg-white/[0.06]"
                                       placeholder="Buscar jugador..."
                                       autocomplete="off"
                                       value="{{ $playerB?->full_name ?? '' }}"
                                       data-name="player_b">
                                <button type="button" class="player-search-clear absolute right-3 top-1/2 -translate-y-1/2 text-white/20 hover:text-white/60 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <div class="player-search-dropdown absolute top-full left-0 right-0 mt-1 z-50 max-h-60 overflow-y-auto rounded-xl bg-[#0f0f0f] border border-white/[0.08] shadow-2xl hidden">
                                @foreach($players as $p)
                                    <button type="button"
                                            class="player-search-item w-full text-left px-4 py-2.5 text-sm text-white/60 hover:bg-white/[0.06] transition-colors flex items-center justify-between gap-3"
                                            data-value="{{ $p->id }}"
                                            data-label="{{ $p->full_name }}"
                                            data-search="{{ strtolower($p->full_name . ' ' . $p->country_code) }}">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <span class="font-medium truncate">{{ $p->full_name }}</span>
                                            <span class="text-xs text-white/30 shrink-0">{{ $p->country_code }}</span>
                                        </div>
                                        <div class="flex items-center gap-3 shrink-0 text-xs">
                                            @if($p->world_ranking)
                                                <span class="text-amber-400/80 font-semibold">#{{ $p->world_ranking }}</span>
                                            @endif
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-8 py-3 rounded-xl font-bold text-sm transition-all duration-200 {{ $playerA && $playerB ? 'bg-sport-500/20 text-sport-400 border border-sport-500/30 hover:bg-sport-500/30 hover:border-sport-500/50 cursor-pointer' : 'bg-white/[0.04] text-white/20 border border-white/[0.06] cursor-not-allowed' }}"
                            {{ !$playerA || !$playerB ? 'disabled' : '' }}>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                        Comparar Estadísticas
                    </button>
                </div>
            </form>
        </div>

        {{-- Results --}}
        @if($playerA && $playerB && $stats)
            <div class="space-y-10 sm:space-y-12">

                {{-- Player Matchup Header --}}
                <div class="flex flex-col sm:flex-row items-center justify-center gap-6 sm:gap-10 py-4 sm:py-6">
                    <div class="text-center">
                        <a href="{{ route('players.show', $playerA) }}" class="text-2xl sm:text-3xl font-bold tracking-tight text-white/90 hover:text-sport-400 transition-colors">{{ $playerA->full_name }}</a>
                        <div class="flex items-center justify-center gap-2 mt-2">
                            <span class="badge-glass">{{ $playerA->country_code }}</span>
                            @if($playerA->world_ranking)
                                <span class="badge-amber-glass">#{{ $playerA->world_ranking }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-col items-center gap-2 shrink-0">
                        <div class="relative flex items-center justify-center w-12 h-12 sm:w-16 sm:h-16 rounded-full border-2 border-white/10 bg-white/[0.03]">
                            <span class="text-sm sm:text-base font-black tracking-wider text-white/40">VS</span>
                        </div>
                    </div>

                    <div class="text-center">
                        <a href="{{ route('players.show', $playerB) }}" class="text-2xl sm:text-3xl font-bold tracking-tight text-white/90 hover:text-sport-400 transition-colors">{{ $playerB->full_name }}</a>
                        <div class="flex items-center justify-center gap-2 mt-2">
                            <span class="badge-glass">{{ $playerB->country_code }}</span>
                            @if($playerB->world_ranking)
                                <span class="badge-amber-glass">#{{ $playerB->world_ranking }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Career Overview --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <x-player-card :player="$playerA" :stats="$playerAStats" />
                    <x-player-card :player="$playerB" :stats="$playerBStats" />
                </div>

                {{-- Set Differential --}}
                <div class="card-glass p-5 sm:p-6 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-1 h-4 rounded-full bg-sport-500/60"></div>
                        <h3 class="section-heading">Set Differential Promedio</h3>
                    </div>
                    <x-stat-bar
                        :valueA="$stats['setDifferential']['playerA']['avg_differential'] + 3"
                        :valueB="$stats['setDifferential']['playerB']['avg_differential'] + 3"
                        :labelA="$playerA->first_name . ': ' . ($stats['setDifferential']['playerA']['avg_differential'] > 0 ? '+' : '') . $stats['setDifferential']['playerA']['avg_differential']"
                        :labelB="$playerB->first_name . ': ' . ($stats['setDifferential']['playerB']['avg_differential'] > 0 ? '+' : '') . $stats['setDifferential']['playerB']['avg_differential']"
                        :sampleA="$stats['setDifferential']['playerA']['total_matches']"
                        :sampleB="$stats['setDifferential']['playerB']['total_matches']"
                    />
                </div>

                {{-- Win Rate by Round --}}
                <x-round-breakdown
                    :playerA="$playerA"
                    :playerB="$playerB"
                    :breakdown="$stats['winRateByRound']"
                    title="Win Rate por Ronda"
                />

                {{-- Win Rate by Format --}}
                <x-round-breakdown
                    :playerA="$playerA"
                    :playerB="$playerB"
                    :breakdown="$stats['winRateByFormat']"
                    title="Win Rate por Formato"
                />

                {{-- Win Rate vs Opponent Ranking --}}
                <x-round-breakdown
                    :playerA="$playerA"
                    :playerB="$playerB"
                    :breakdown="$stats['winRateVsRankRange']"
                    title="Win Rate vs Rango de Ranking"
                />

                {{-- Recent Form --}}
                <div class="card-glass p-5 sm:p-6 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-1 h-4 rounded-full bg-sport-500/60"></div>
                        <h3 class="section-heading">Forma Reciente (Últimos 10 Partidos)</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        @foreach([$stats['recentForm']['playerA'], $stats['recentForm']['playerB']] as $i => $form)
                            @php $p = $i === 0 ? $playerA : $playerB; @endphp
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-semibold text-white/70">{{ $p->full_name }}</span>
                                    <span class="text-sm font-bold {{ $form['win_rate'] >= 50 ? 'text-emerald-400' : 'text-red-400' }}">{{ $form['win_rate'] }}%</span>
                                </div>
                                <div class="flex gap-1">
                                    @foreach($form['matches']->reverse() as $match)
                                        @php $won = $match->winner_id === $p->id; @endphp
                                        <div class="h-8 flex-1 rounded {{ $won ? 'bg-emerald-500/30' : 'bg-red-500/30' }} flex items-center justify-center">
                                            <span class="text-[10px] font-bold {{ $won ? 'text-emerald-400' : 'text-red-400' }}">{{ $won ? 'W' : 'L' }}</span>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="text-center text-[10px] text-white/25">{{ $form['wins'] }}-{{ $form['losses'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Streaks --}}
                <div class="card-glass p-5 sm:p-6 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-1 h-4 rounded-full bg-sport-500/60"></div>
                        <h3 class="section-heading">Racha Actual</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        @foreach([$stats['streaks']['playerA'], $stats['streaks']['playerB']] as $i => $streak)
                            @php $p = $i === 0 ? $playerA : $playerB; @endphp
                            <div class="text-center space-y-2">
                                <a href="{{ route('players.show', $p) }}" class="text-sm font-semibold text-white/60 hover:text-sport-400 transition-colors">{{ $p->full_name }}</a>
                                @if($streak['type'] === 'W')
                                    <div class="flex items-center justify-center gap-2">
                                        <span class="text-2xl font-black text-emerald-400">{{ $streak['count'] }}</span>
                                        <span class="text-sm font-semibold text-emerald-400/80">W seguidos 🔥</span>
                                    </div>
                                @elseif($streak['type'] === 'L')
                                    <div class="flex items-center justify-center gap-2">
                                        <span class="text-2xl font-black text-red-400">{{ $streak['count'] }}</span>
                                        <span class="text-sm font-semibold text-red-400/80">L seguidos</span>
                                    </div>
                                @else
                                    <p class="text-sm text-white/30">Sin partidos</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Link back to basic compare --}}
                <div class="text-center">
                    <a href="{{ route('compare', ['gender' => $gender, 'player_a' => $playerA->id, 'player_b' => $playerB->id]) }}"
                       class="inline-flex items-center gap-2 text-sm text-white/40 hover:text-sport-400 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        Volver a comparación básica
                    </a>
                </div>

            </div>
        @endif
    </div>

</x-layout>
