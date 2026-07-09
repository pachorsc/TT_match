@props(['rankings'])

<div id="ranking-filter" class="card-glass overflow-hidden">
    <div class="px-5 sm:px-6 py-4 border-b border-gray-200/80 dark:border-white/[0.06] space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-1 h-4 rounded-full bg-sport-500/60"></div>
                <h3 class="section-heading">Rankings Mundiales</h3>
            </div>
            <p class="ranking-filter-count text-xs text-gray-500 dark:text-white/30">{{ count($rankings) }} jugadores</p>
        </div>
        <div class="relative max-w-sm">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-white/20 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
            <input type="text"
                   class="ranking-filter-input w-full bg-gray-100/60 dark:bg-white/[0.04] border border-gray-200/80 dark:border-white/[0.08] rounded-xl pl-10 pr-4 py-2.5 text-sm outline-none transition-all duration-200 placeholder:text-gray-400 dark:placeholder:text-white/20 focus:border-sport-500/40 focus:bg-gray-200/50 dark:focus:bg-white/[0.06] text-gray-700 dark:text-white/80"
                   placeholder="Buscar por nombre o país..."
                   autocomplete="off">
        </div>
    </div>

    @if($rankings->isEmpty())
        <x-empty-state message="No ranking data available." />
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200/80 dark:border-white/[0.06]">
                        <th class="text-center px-5 sm:px-6 py-3 text-xs font-semibold uppercase tracking-[0.1em] text-gray-500 dark:text-white/30 w-16">#</th>
                        <th class="text-left px-5 sm:px-6 py-3 text-xs font-semibold uppercase tracking-[0.1em] text-gray-500 dark:text-white/30">Jugador</th>
                        <th class="text-left px-5 sm:px-6 py-3 text-xs font-semibold uppercase tracking-[0.1em] text-gray-500 dark:text-white/30">País</th>
                        <th class="text-right px-5 sm:px-6 py-3 text-xs font-semibold uppercase tracking-[0.1em] text-gray-500 dark:text-white/30">Puntos</th>
                        <th class="text-center px-5 sm:px-6 py-3 text-xs font-semibold uppercase tracking-[0.1em] text-gray-500 dark:text-white/30">Δ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200/70 dark:divide-white/[0.04]">
                    @foreach($rankings as $ranking)
                        @php
                            $searchData = strtolower($ranking->player->full_name . ' ' . $ranking->player->country_code . ' ' . $ranking->player->country);
                        @endphp
                        <tr class="ranking-row hover:bg-gray-50/80 dark:hover:bg-white/[0.02] transition-colors" data-search="{{ $searchData }}">
                            <td class="px-5 sm:px-6 py-4 text-center">
                                <span class="badge-amber-glass">{{ $ranking->ranking }}</span>
                            </td>
                            <td class="px-5 sm:px-6 py-4">
                                <a href="{{ route('players.show', $ranking->player_id) }}" class="font-semibold text-gray-900 dark:text-white/90 hover:text-sport-400 transition-colors">
                                    {{ $ranking->player->full_name }}
                                </a>
                            </td>
                            <td class="px-5 sm:px-6 py-4">
                                <span class="badge-glass">{{ $ranking->player->country_code }}</span>
                            </td>
                            <td class="px-5 sm:px-6 py-4 text-right font-mono font-semibold text-gray-600 dark:text-white/70">
                                {{ number_format($ranking->rating_points) }}
                            </td>
                            <td class="px-5 sm:px-6 py-4 text-center">
                                @if($ranking->movement === null)
                                    <span class="text-gray-400 dark:text-white/20">—</span>
                                @elseif($ranking->movement > 0)
                                    <span class="text-emerald-400 font-semibold">↑{{ $ranking->movement }}</span>
                                @elseif($ranking->movement < 0)
                                    <span class="text-red-400 font-semibold">↓{{ abs($ranking->movement) }}</span>
                                @else
                                    <span class="text-gray-500 dark:text-white/30">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
