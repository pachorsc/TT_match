<x-layout title="TT Match — Table Tennis Match Preview">

    <div class="space-y-8 sm:space-y-10">
        <div class="text-center space-y-2">
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white">Partidos</h1>
            <p class="text-sm sm:text-base text-white/40">Select a match to view the details</p>
        </div>

        @if($matches->isEmpty())
            <x-empty-state message="No completed matches available yet." />
        @else
            <div class="grid grid-cols-1 gap-3">
                @foreach($matches as $match)
                    @php
                        $winnerId = $match->winner_id;
                    @endphp
                    <a href="{{ route('matches.show', $match->id) }}"
                       class="group relative card-glass-accent overflow-hidden">

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-5 sm:p-6 gap-4 sm:gap-6">
                            <div class="flex items-center gap-4 sm:gap-6 flex-1 min-w-0">
                                <div class="text-right min-w-0 flex-1 sm:flex-none sm:w-[160px]">
                                    <p class="font-bold text-sm sm:text-base text-white/90 group-hover:text-sport-400 transition-colors duration-200 truncate">
                                        {{ $match->playerA->full_name }}
                                    </p>
                                    <p class="text-xs text-white/30 mt-0.5">
                                        {{ $match->playerA->country_code }}
                                        @if($match->playerA->world_ranking) · #{{ $match->playerA->world_ranking }}@endif
                                    </p>
                                </div>

                                <div class="flex flex-col items-center shrink-0">
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-xl sm:text-2xl font-black {{ $winnerId && $winnerId === $match->player_a_id ? 'text-emerald-400' : 'text-white/70' }}">
                                            {{ $match->player_a_sets }}
                                        </span>
                                        <span class="text-xs font-semibold text-white/20">—</span>
                                        <span class="text-xl sm:text-2xl font-black {{ $winnerId && $winnerId === $match->player_b_id ? 'text-emerald-400' : 'text-white/70' }}">
                                            {{ $match->player_b_sets }}
                                        </span>
                                    </div>
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-white/20 mt-0.5">VS</span>
                                </div>

                                <div class="min-w-0 flex-1 sm:flex-none sm:w-[160px]">
                                    <p class="font-bold text-sm sm:text-base text-white/90 group-hover:text-sport-400 transition-colors duration-200 truncate">
                                        {{ $match->playerB->full_name }}
                                    </p>
                                    <p class="text-xs text-white/30 mt-0.5">
                                        {{ $match->playerB->country_code }}
                                        @if($match->playerB->world_ranking) · #{{ $match->playerB->world_ranking }}@endif
                                    </p>
                                </div>
                            </div>

                            <div class="hidden sm:flex flex-col items-end shrink-0">
                                <p class="text-sm font-semibold text-white/50">{{ $match->tournament->name }}</p>
                                <p class="text-xs text-white/30 mt-0.5">{{ $match->match_date->format('M d, Y') }} · {{ $match->round }}</p>
                            </div>

                            <svg class="hidden sm:block w-5 h-5 text-white/20 group-hover:text-sport-400/60 transition-all duration-200 group-hover:translate-x-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </div>

                        {{-- Mobile-only bottom row --}}
                        <div class="sm:hidden flex items-center justify-between px-5 pb-4 pt-0">
                            <p class="text-xs text-white/40">{{ $match->tournament->name }}</p>
                            <p class="text-xs text-white/30">{{ $match->match_date->format('M d, Y') }} · {{ $match->round }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

</x-layout>
