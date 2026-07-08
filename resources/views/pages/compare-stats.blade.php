<x-layout title="Estadísticas Avanzadas — Comparar Jugadores">

    <div class="space-y-6 sm:space-y-8">

        {{-- Header --}}
        <div class="text-center space-y-2">
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white">
                Estadísticas Avanzadas
            </h1>
            <p class="text-sm text-white/35 max-w-md mx-auto">Comparación detallada entre dos jugadores — rendimiento, forma y rachas</p>
        </div>

        {{-- Gender Toggle + Player Selectors --}}
        <div class="card-glass p-4 sm:p-5">

            {{-- Gender Tabs --}}
            <div class="flex items-center justify-center gap-2 mb-5">
                <a href="{{ route('compare.stats', ['gender' => 'M', 'player_a' => request('player_a'), 'player_b' => request('player_b')]) }}"
                   class="px-5 py-1.5 rounded-xl text-sm font-bold transition-all duration-200
                   {{ $gender === 'M' ? 'bg-sport-500/20 text-sport-400 border border-sport-500/30' : 'bg-white/[0.04] text-white/50 hover:text-white/70 hover:bg-white/[0.06] border border-transparent' }}>
                    ♂ Masculino
                </a>
                <a href="{{ route('compare.stats', ['gender' => 'F', 'player_a' => request('player_a'), 'player_b' => request('player_b')]) }}"
                   class="px-5 py-1.5 rounded-xl text-sm font-bold transition-all duration-200
                   {{ $gender === 'F' ? 'bg-sport-500/20 text-sport-400 border border-sport-500/30' : 'bg-white/[0.04] text-white/50 hover:text-white/70 hover:bg-white/[0.06] border border-transparent' }}">
                    ♀ Femenino
                </a>
            </div>

            {{-- Player Selectors --}}
            <form method="GET" action="{{ route('compare.stats') }}" class="space-y-4">
                <input type="hidden" name="gender" value="{{ $gender }}">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    {{-- Player A --}}
                    <div>
                        <label class="block text-[10px] font-semibold uppercase tracking-[0.1em] text-white/30 mb-1.5">Jugador A</label>
                        <div class="player-search relative">
                            <input type="hidden" name="player_a" class="player-search-hidden" value="{{ $playerA?->id }}">
                            <div class="relative">
                                <input type="text"
                                       class="player-search-input w-full bg-white/[0.04] border border-white/[0.08] rounded-xl px-3 py-2.5 pr-10 text-sm outline-none transition-all duration-200 placeholder:text-white/20 focus:border-sport-500/40 focus:bg-white/[0.06]"
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
                        <label class="block text-[10px] font-semibold uppercase tracking-[0.1em] text-white/30 mb-1.5">Jugador B</label>
                        <div class="player-search relative">
                            <input type="hidden" name="player_b" class="player-search-hidden" value="{{ $playerB?->id }}">
                            <div class="relative">
                                <input type="text"
                                       class="player-search-input w-full bg-white/[0.04] border border-white/[0.08] rounded-xl px-3 py-2.5 pr-10 text-sm outline-none transition-all duration-200 placeholder:text-white/20 focus:border-sport-500/40 focus:bg-white/[0.06]"
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
                            class="inline-flex items-center gap-2 px-7 py-2.5 rounded-xl font-bold text-sm transition-all duration-200 {{ $playerA && $playerB ? 'bg-sport-500/20 text-sport-400 border border-sport-500/30 hover:bg-sport-500/30 hover:border-sport-500/50 cursor-pointer' : 'bg-white/[0.04] text-white/20 border border-white/[0.06] cursor-not-allowed' }}"
                            {{ !$playerA || !$playerB ? 'disabled' : '' }}>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                        Comparar Estadísticas
                    </button>
                </div>
            </form>
        </div>

        {{-- ═══════════════ RESULTS ═══════════════ --}}
        @if($playerA && $playerB && $stats)
            <div class="space-y-6 sm:space-y-8">

                {{-- VS Header --}}
                <div class="relative flex items-center justify-center gap-4 sm:gap-8 py-6 animate-fade-in">
                    {{-- Player A Side --}}
                    <div class="flex-1 text-right min-w-0">
                        <a href="{{ route('players.show', $playerA) }}" class="text-lg sm:text-2xl font-black tracking-tight text-white/90 hover:text-sport-400 transition-colors block truncate">
                            {{ $playerA->full_name }}
                        </a>
                        <div class="flex items-center justify-end gap-2 mt-2">
                            <span class="badge-glass text-[10px] px-2 py-0.5">{{ $playerA->country_code }}</span>
                            @if($playerA->world_ranking)
                                <span class="badge-amber-glass text-[10px] px-2 py-0.5">#{{ $playerA->world_ranking }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- VS Circle --}}
                    <div class="relative shrink-0">
                        <div class="w-14 h-14 rounded-full border-2 border-white/10 bg-white/[0.03] flex items-center justify-center">
                            <span class="text-sm font-black tracking-widest text-white/30">VS</span>
                        </div>
                        <div class="absolute -inset-px rounded-full border border-white/[0.04]"></div>
                    </div>

                    {{-- Player B Side --}}
                    <div class="flex-1 text-left min-w-0">
                        <a href="{{ route('players.show', $playerB) }}" class="text-lg sm:text-2xl font-black tracking-tight text-white/90 hover:text-sport-400 transition-colors block truncate">
                            {{ $playerB->full_name }}
                        </a>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="badge-glass text-[10px] px-2 py-0.5">{{ $playerB->country_code }}</span>
                            @if($playerB->world_ranking)
                                <span class="badge-amber-glass text-[10px] px-2 py-0.5">#{{ $playerB->world_ranking }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Career Overview --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 animate-fade-slide-up delay-100">
                    <x-player-card :player="$playerA" :stats="$playerAStats" :streak="$stats['streaks']['playerA']" compact />
                    <x-player-card :player="$playerB" :stats="$playerBStats" :streak="$stats['streaks']['playerB']" compact />
                </div>

                {{-- Set Differential --}}
                <div class="card-glass p-5 sm:p-6 space-y-4 animate-fade-slide-up delay-200">
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
                        highlight
                    />
                </div>

                {{-- Key Insights --}}
                @php
                    $insights = [];
                    $a = $playerA->first_name;
                    $b = $playerB->first_name;

                    // Round insights
                    foreach($stats['winRateByRound'] as $round => $data) {
                        if($data['playerA']['total'] >= 3 && $data['playerB']['total'] >= 3) {
                            $diff = $data['playerA']['win_rate'] - $data['playerB']['win_rate'];
                            if(abs($diff) >= 10) {
                                $winner = $diff > 0 ? $a : $b;
                                $insights[] = ['icon' => 'trophy', 'highlight' => $winner, 'text' => "es dominante en $round (" . round(abs($diff)) . "% de ventaja)"];
                            }
                        }
                    }

                    // Format insights
                    foreach($stats['winRateByFormat'] as $format => $data) {
                        if($data['playerA']['total'] >= 3 && $data['playerB']['total'] >= 3) {
                            $diff = $data['playerA']['win_rate'] - $data['playerB']['win_rate'];
                            if(abs($diff) >= 10) {
                                $winner = $diff > 0 ? $a : $b;
                                $insights[] = ['icon' => 'chart-bar', 'highlight' => $winner, 'text' => "tiene mejor rendimiento en $format (" . round(abs($diff)) . "% de ventaja)"];
                            }
                        }
                    }

                    // Set differential insight
                    $diffA = $stats['setDifferential']['playerA']['avg_differential'];
                    $diffB = $stats['setDifferential']['playerB']['avg_differential'];
                    if(abs($diffA - $diffB) >= 0.3) {
                        $winner = $diffA > $diffB ? $a : $b;
                        $insights[] = ['icon' => 'trending-up', 'highlight' => $winner, 'text' => "tiene una ventaja de " . number_format(abs($diffA - $diffB), 1) . " en set differential promedio"];
                    }

                    // Win rate vs rank insight
                    foreach($stats['winRateVsRankRange'] as $range => $data) {
                        if($data['playerA']['total'] >= 3 && $data['playerB']['total'] >= 3) {
                            $diff = $data['playerA']['win_rate'] - $data['playerB']['win_rate'];
                            if(abs($diff) >= 15) {
                                $winner = $diff > 0 ? $a : $b;
                                $insights[] = ['icon' => 'check-badge', 'highlight' => $winner, 'text' => "supera a su rival contra rivales del $range (" . round(abs($diff)) . "% de ventaja)"];
                            }
                        }
                    }
                @endphp

                @if(count($insights) > 0)
                    <div class="space-y-3 animate-fade-slide-up delay-300">
                        <div class="flex items-center gap-3">
                            <div class="w-1 h-4 rounded-full bg-sport-500/60"></div>
                            <h3 class="section-heading">Key Insights</h3>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach(array_slice($insights, 0, 4) as $insight)
                                <x-comparison-insight
                                    :icon="$insight['icon']"
                                    :highlight="$insight['highlight']"
                                    :text="$insight['text']"
                                />
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Win Rate by Round --}}
                <div class="animate-fade-slide-up delay-300">
                    <x-round-breakdown
                        :playerA="$playerA"
                        :playerB="$playerB"
                        :breakdown="$stats['winRateByRound']"
                        title="Win Rate por Ronda"
                    />
                </div>

                {{-- Win Rate by Format --}}
                <div class="animate-fade-slide-up delay-400">
                    <x-round-breakdown
                        :playerA="$playerA"
                        :playerB="$playerB"
                        :breakdown="$stats['winRateByFormat']"
                        title="Win Rate por Formato"
                    />
                </div>

                {{-- Win Rate vs Opponent Ranking --}}
                <div class="animate-fade-slide-up delay-500">
                    <x-round-breakdown
                        :playerA="$playerA"
                        :playerB="$playerB"
                        :breakdown="$stats['winRateVsRankRange']"
                        title="Win Rate vs Rango de Ranking"
                    />
                </div>

                {{-- Recent Form + Streaks --}}
                <div class="card-glass p-5 sm:p-6 space-y-5 animate-fade-slide-up">
                    <div class="flex items-center gap-3">
                        <div class="w-1 h-4 rounded-full bg-sport-500/60"></div>
                        <h3 class="section-heading">Forma Reciente</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        @foreach([$stats['recentForm']['playerA'], $stats['recentForm']['playerB']] as $i => $form)
                            @php
                                $p = $i === 0 ? $playerA : $playerB;
                                $streak = $i === 0 ? $stats['streaks']['playerA'] : $stats['streaks']['playerB'];
                            @endphp
                            <div class="space-y-3">
                                {{-- Player name + streak --}}
                                <div class="flex items-center justify-between">
                                    <a href="{{ route('players.show', $p) }}" class="text-sm font-semibold text-white/70 hover:text-sport-400 transition-colors">{{ $p->full_name }}</a>
                                    <x-streak-badge :streak="$streak" />
                                </div>

                                {{-- Form bar --}}
                                <div class="flex gap-1">
                                    @foreach($form['matches']->reverse() as $match)
                                        @php $won = $match->winner_id === $p->id; @endphp
                                        <div class="form-block {{ $won ? 'win' : 'loss' }}" title="{{ $match->tournament?->name ?? 'Partido' }} — {{ $match->match_date?->format('d/m/Y') ?? '' }}">
                                            <span class="text-[10px] font-bold {{ $won ? 'text-emerald-400' : 'text-red-400' }}">{{ $won ? 'W' : 'L' }}</span>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Win rate + record --}}
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="text-white/30">{{ $form['wins'] }}W - {{ $form['losses'] }}L</span>
                                    <span class="font-bold {{ $form['win_rate'] >= 60 ? 'text-emerald-400' : ($form['win_rate'] >= 45 ? 'text-amber-400' : 'text-red-400') }}">{{ $form['win_rate'] }}%</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Link back --}}
                <div class="text-center pt-2">
                    <a href="{{ route('compare', ['gender' => $gender, 'player_a' => $playerA->id, 'player_b' => $playerB->id]) }}"
                       class="inline-flex items-center gap-2 text-sm text-white/30 hover:text-sport-400 transition-colors">
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
