<x-layout title="Jugadores">

    <div class="space-y-10 sm:space-y-12">

        <div class="text-center space-y-2">
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-gray-900 dark:text-white">Jugadores</h1>
            <p class="text-sm sm:text-base text-gray-400 dark:text-white/40">Selecciona un jugador para ver su perfil completo</p>
        </div>

        {{-- Search + Grid --}}
        <div id="player-filter" class="space-y-6">
            <div class="max-w-md mx-auto space-y-4">
                <div class="relative">
                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-white/20 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <input type="text"
                           class="player-filter-input w-full bg-gray-100/60 dark:bg-white/[0.04] border border-gray-200/80 dark:border-white/[0.08] rounded-xl pl-11 pr-4 py-3 text-sm outline-none transition-all duration-200 placeholder:text-gray-400 dark:placeholder:text-white/20 focus:border-sport-500/40 focus:bg-gray-200/50 dark:focus:bg-white/[0.06] text-gray-700 dark:text-white/80"
                           placeholder="Buscar jugador..."
                           autocomplete="off">
                </div>
                <p class="player-filter-count text-xs text-gray-500 dark:text-white/30 text-center">{{ count($players) }} jugadores</p>
            </div>

            {{-- Gender Tabs --}}
            <div class="flex items-center justify-center gap-2">
                <a href="{{ route('players.index', ['gender' => 'M']) }}"
                   class="px-5 py-2 rounded-xl text-sm font-bold transition-all duration-200
                   {{ $gender === 'M' ? 'bg-sport-500/20 text-sport-400 border border-sport-500/30' : 'bg-gray-100/60 dark:bg-white/[0.04] text-gray-500 dark:text-white/50 hover:text-gray-700 dark:hover:text-white/70 hover:bg-gray-200/60 dark:hover:bg-white/[0.06] border border-transparent' }}">
                    Masculino
                </a>
                <a href="{{ route('players.index', ['gender' => 'F']) }}"
                   class="px-5 py-2 rounded-xl text-sm font-bold transition-all duration-200
                   {{ $gender === 'F' ? 'bg-sport-500/20 text-sport-400 border border-sport-500/30' : 'bg-gray-100/60 dark:bg-white/[0.04] text-gray-500 dark:text-white/50 hover:text-gray-700 dark:hover:text-white/70 hover:bg-gray-200/60 dark:hover:bg-white/[0.06] border border-transparent' }}">
                    Femenino
                </a>
            </div>

            {{-- Player Grid --}}
            @if($players->isEmpty())
                <x-empty-state message="No players found." />
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
                @foreach($players as $player)
                    @php
                        $searchData = strtolower($player->full_name . ' ' . $player->country_code . ' ' . $player->country . ' #' . $player->world_ranking);
                    @endphp
                    <a href="{{ route('players.show', $player) }}"
                       class="player-card card-glass p-5 sm:p-6 space-y-4 transition-all duration-200 group"
                       data-search="{{ $searchData }}">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="text-base font-bold tracking-tight text-gray-900 dark:text-white/90 group-hover:text-sport-400 transition-colors truncate">
                                {{ $player->full_name }}
                            </h3>
                            <span class="badge-glass shrink-0">{{ $player->country_code }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-4 text-sm">
                            <div class="space-y-1">
                                <span class="stat-label">Ranking</span>
                                <p class="font-bold text-gray-700 dark:text-white/80">{{ $player->world_ranking ? '#' . $player->world_ranking : '—' }}</p>
                            </div>
                            <div class="space-y-1 text-right">
                                <span class="stat-label">Rating</span>
                                <p class="font-bold text-gray-700 dark:text-white/80">{{ $player->rating_points ? number_format($player->rating_points) : '—' }}</p>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

</x-layout>
