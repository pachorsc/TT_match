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
                            class="predict-submit inline-flex items-center gap-2 px-8 py-3 rounded-xl font-bold text-sm transition-all duration-200 {{ $playerA && $playerB ? 'bg-sport-500/20 text-sport-400 border border-sport-500/30 hover:bg-sport-500/30 hover:border-sport-500/50 cursor-pointer' : 'bg-white/[0.04] text-white/20 border border-white/[0.06] cursor-not-allowed' }}"
                            {{ !$playerA || !$playerB ? 'opacity-40' : '' }}>
                        <svg class="w-4 h-4 predict-submit-icon" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        <span class="predict-submit-text">Comparar</span>
                        <svg class="w-4 h-4 predict-submit-spinner hidden animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
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

                {{-- Link to Advanced Stats --}}
                <div class="text-center">
                    <a href="{{ route('compare.stats', ['gender' => $gender, 'player_a' => $playerA->id, 'player_b' => $playerB->id]) }}"
                       class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-bold bg-sport-500/10 text-sport-400 border border-sport-500/20 hover:bg-sport-500/20 hover:border-sport-500/40 transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                        Ver Estadísticas Avanzadas
                    </a>
                </div>

                {{-- Latest Videos --}}
                <div class="space-y-5">
                    <div class="flex items-center gap-3">
                        <div class="w-1 h-4 rounded-full bg-sport-500/60"></div>
                        <h2 class="section-heading">Latest Videos</h2>
                    </div>

                    <div class="videos-container"
                         data-players='{{ json_encode([$playerAData["player"]->id, $playerBData["player"]->id]) }}'
                         data-player-names='{{ json_encode([$playerAData["player"]->id => $playerAData["player"]->full_name, $playerBData["player"]->id => $playerBData["player"]->full_name]) }}'>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                            @foreach([$playerAData, $playerBData] as $pData)
                                <div class="videos-player-slot" data-player-id="{{ $pData['player']->id }}">
                            <div class="flex items-center gap-3 mb-4">
                                <span class="text-sm font-semibold text-white/60">{{ $pData['player']->full_name }}</span>
                                <span class="text-xs text-white/20">—</span>
                                <span class="text-xs text-white/30">YouTube</span>
                            </div>
                                    <div class="videos-spinner flex items-center justify-center py-16">
                                        <svg class="w-8 h-8 text-sport-400 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                    <div class="videos-grid grid grid-cols-1 gap-4 hidden"></div>
                                    <div class="videos-empty hidden">
                                        <div class="card-glass px-6 py-14 sm:py-16 text-center space-y-4">
                                            <svg class="w-10 h-10 mx-auto text-white/15" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z" />
                                            </svg>
                                            <p class="text-sm text-white/30 max-w-xs mx-auto">No videos found for this player.</p>
                                        </div>
                                    </div>
                                    <div class="videos-error hidden">
                                        <div class="card-glass px-6 py-14 sm:py-16 text-center space-y-4">
                                            <svg class="w-10 h-10 mx-auto text-white/15" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                            </svg>
                                            <p class="text-sm text-white/30 max-w-xs mx-auto">Error loading videos. Try again.</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>
        @endif
    </div>

</x-layout>
