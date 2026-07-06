<x-layout title="Videos — WTT YouTube">

    <div class="space-y-10 sm:space-y-12">

        {{-- Header --}}
        <div class="text-center space-y-2">
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white">Videos</h1>
            <p class="text-sm sm:text-base text-white/40">Busca los últimos videos de los jugadores en el canal oficial de la WTT</p>
        </div>

        {{-- Player Selector --}}
        <div class="card-glass p-5 sm:p-8">
            <div class="max-w-md mx-auto space-y-4">
                <label class="block text-xs font-semibold uppercase tracking-[0.1em] text-white/30 mb-2">Jugador</label>
                <div class="player-search relative">
                    <input type="hidden" name="player_id" class="player-search-hidden" value="">
                    <div class="relative">
                        <input type="text"
                               class="player-search-input w-full bg-white/[0.04] border border-white/[0.08] rounded-xl px-4 py-3 pr-10 text-sm outline-none transition-all duration-200 placeholder:text-white/20 focus:border-sport-500/40 focus:bg-white/[0.06]"
                               placeholder="Buscar jugador..."
                               autocomplete="off">
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
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>

                <button type="button"
                        id="videos-search-btn"
                        class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-bold text-sm text-sport-400 bg-sport-500/15 border border-sport-500/25 hover:bg-sport-500/25 hover:border-sport-500/40 transition-all duration-200 disabled:opacity-40 disabled:cursor-not-allowed"
                        disabled>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    Buscar videos
                </button>
            </div>
        </div>

        {{-- Results --}}
        <div class="videos-container space-y-8" data-players="[]">
            {{-- Populated by JS --}}
        </div>

    </div>

</x-layout>
