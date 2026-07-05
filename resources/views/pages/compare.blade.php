<x-layout title="Predicción — Comparar Jugadores">

    <div class="space-y-10 sm:space-y-12">

        {{-- Header --}}
        <div class="text-center space-y-2">
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white">Comparar Jugadores</h1>
            <p class="text-sm sm:text-base text-white/40">Selecciona dos jugadores para ver su enfrentamiento</p>
        </div>

        {{-- Gender Toggle + Player Selectors --}}
        <div class="card-glass p-5 sm:p-8">

            {{-- Gender Tabs --}}
            <div class="flex items-center justify-center gap-2 mb-6 sm:mb-8">
                @php
                    $currentGender = $gender ?? 'M';
                @endphp
                <a href="{{ route('compare', ['gender' => 'M', 'player_a' => request('player_a'), 'player_b' => request('player_b')]) }}"
                   class="px-5 py-2 rounded-xl text-sm font-bold transition-all duration-200
                   {{ $currentGender === 'M' ? 'bg-sport-500/20 text-sport-400 border border-sport-500/30' : 'bg-white/[0.04] text-white/50 hover:text-white/70 hover:bg-white/[0.06] border border-transparent' }}">
                    ♂ Masculino
                </a>
                <a href="{{ route('compare', ['gender' => 'F', 'player_a' => request('player_a'), 'player_b' => request('player_b')]) }}"
                   class="px-5 py-2 rounded-xl text-sm font-bold transition-all duration-200
                   {{ $currentGender === 'F' ? 'bg-sport-500/20 text-sport-400 border border-sport-500/30' : 'bg-white/[0.04] text-white/50 hover:text-white/70 hover:bg-white/[0.06] border border-transparent' }}">
                    ♀ Femenino
                </a>
            </div>

            {{-- Player Selectors --}}
            <form method="GET" action="{{ route('compare') }}" class="space-y-6" id="predict-form">
                <input type="hidden" name="gender" value="{{ $currentGender }}">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    {{-- Player A --}}
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-[0.1em] text-white/30 mb-2">Jugador A</label>
                        <div class="player-search relative">
                            <input type="hidden" name="player_a" class="player-search-hidden"
                                   value="{{ $playerA?->id }}">
                            <div class="relative">
                                <input type="text"
                                       class="player-search-input w-full bg-white/[0.04] border border-white/[0.08] rounded-xl px-4 py-3 pr-10 text-sm outline-none transition-all duration-200 placeholder:text-white/20 focus:border-sport-500/40 focus:bg-white/[0.06]"
                                       placeholder="Buscar jugador..."
                                       autocomplete="off"
                                       value="{{ $playerA?->full_name ?? '' }}"
                                       data-name="player_a">
                                <button type="button"
                                        class="player-search-clear absolute right-3 top-1/2 -translate-y-1/2 text-white/20 hover:text-white/60 transition-colors">
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
                                            @if($p->rating_points)
                                                <span class="text-white/30">{{ number_format($p->rating_points) }}pts</span>
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
                            <input type="hidden" name="player_b" class="player-search-hidden"
                                   value="{{ $playerB?->id }}">
                            <div class="relative">
                                <input type="text"
                                       class="player-search-input w-full bg-white/[0.04] border border-white/[0.08] rounded-xl px-4 py-3 pr-10 text-sm outline-none transition-all duration-200 placeholder:text-white/20 focus:border-sport-500/40 focus:bg-white/[0.06]"
                                       placeholder="Buscar jugador..."
                                       autocomplete="off"
                                       value="{{ $playerB?->full_name ?? '' }}"
                                       data-name="player_b">
                                <button type="button"
                                        class="player-search-clear absolute right-3 top-1/2 -translate-y-1/2 text-white/20 hover:text-white/60 transition-colors">
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
                                            @if($p->rating_points)
                                                <span class="text-white/30">{{ number_format($p->rating_points) }}pts</span>
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
                            class="predict-submit inline-flex items-center gap-2 px-8 py-3 rounded-xl font-bold text-sm transition-all duration-200 {{ $playerA && $playerB ? 'bg-sport-500/20 text-sport-400 border border-sport-500/30 hover:bg-sport-500/30 hover:border-sport-500/50 cursor-pointer' : 'bg-white/[0.04] text-white/20 border border-white/[0.06] cursor-not-allowed' }}
                            {{ !$playerA || !$playerB ? 'opacity-40' : '' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        Comparar
                    </button>
                </div>
            </form>
        </div>

        {{-- Results --}}
        @if($playerA && $playerB)
            <div class="space-y-10 sm:space-y-12">

                {{-- Player Matchup Header --}}
                <div class="flex flex-col sm:flex-row items-center justify-center gap-6 sm:gap-10 py-4 sm:py-6">
                    <x-player-header :player="$playerAData['player']" :rankingMovement="$playerAData['rankingMovement']" />

                    <div class="flex flex-col items-center gap-2 shrink-0">
                        <div class="relative flex items-center justify-center w-12 h-12 sm:w-16 sm:h-16 rounded-full border-2 border-white/10 bg-white/[0.03]">
                            <span class="text-sm sm:text-base font-black tracking-wider text-white/40">VS</span>
                        </div>
                    </div>

                    <x-player-header :player="$playerBData['player']" :rankingMovement="$playerBData['rankingMovement']" />
                </div>

                {{-- Player Cards --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <x-player-card :player="$playerAData['player']" :stats="$playerAData['stats']" />
                    <x-player-card :player="$playerBData['player']" :stats="$playerBData['stats']" />
                </div>

                {{-- Last 7 Matches --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <x-match-table :player="$playerAData['player']" :matches="$playerAData['last7']" />
                    <x-match-table :player="$playerBData['player']" :matches="$playerBData['last7']" />
                </div>

                {{-- Head to Head --}}
                <x-head-to-head
                    :playerA="$playerAData['player']"
                    :playerB="$playerBData['player']"
                    :headToHead="$headToHead"
                />

                {{-- Latest News --}}
                <div class="space-y-5">
                    <div class="flex items-center gap-3">
                        <div class="w-1 h-4 rounded-full bg-sport-500/60"></div>
                        <h2 class="section-heading">Latest News</h2>
                    </div>

                    @if($news->isEmpty())
                        <x-empty-state message="No recent news available." />
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($news as $item)
                                <x-news-card :news="$item" />
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>
        @endif
    </div>

</x-layout>
