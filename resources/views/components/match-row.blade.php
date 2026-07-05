@props(['match', 'index' => 0])

@php
    $winnerId = $match->winner_id;
@endphp

<a href="{{ route('matches.show', $match->id) }}"
   data-match-index="{{ $index }}"
   class="group flex items-center justify-between px-5 sm:px-6 py-3.5 sm:py-4 transition-all duration-200 hover:bg-white/[0.02]">

    {{-- Players + Score --}}
    <div class="flex items-center gap-3 sm:gap-5 flex-1 min-w-0">
        {{-- Player A --}}
        <div class="text-right min-w-0 flex-1 sm:flex-none sm:w-[140px]">
            <p class="text-sm font-semibold {{ $winnerId && $winnerId === $match->player_a_id ? 'text-white/90' : 'text-white/50' }} group-hover:text-white/90 transition-colors truncate">
                {{ $match->playerA->full_name }}
            </p>
            <p class="text-xs text-white/30 mt-px">
                {{ $match->playerA->country_code }}
                @if($match->playerA->world_ranking)· #{{ $match->playerA->world_ranking }}@endif
            </p>
        </div>

        {{-- Score --}}
        <div class="flex flex-col items-center shrink-0">
            <div class="flex items-baseline gap-1.5">
                <span class="text-lg sm:text-xl font-black leading-none {{ $winnerId && $winnerId === $match->player_a_id ? 'text-emerald-400' : 'text-white/40' }}">
                    {{ $match->player_a_sets }}
                </span>
                <span class="text-xs font-semibold text-white/20">:</span>
                <span class="text-lg sm:text-xl font-black leading-none {{ $winnerId && $winnerId === $match->player_b_id ? 'text-emerald-400' : 'text-white/40' }}">
                    {{ $match->player_b_sets }}
                </span>
            </div>
            <span class="text-[10px] font-bold uppercase tracking-widest text-white/15 mt-0.5">VS</span>
        </div>

        {{-- Player B --}}
        <div class="min-w-0 flex-1 sm:flex-none sm:w-[140px]">
            <p class="text-sm font-semibold {{ $winnerId && $winnerId === $match->player_b_id ? 'text-white/90' : 'text-white/50' }} group-hover:text-white/90 transition-colors truncate">
                {{ $match->playerB->full_name }}
            </p>
            <p class="text-xs text-white/30 mt-px">
                {{ $match->playerB->country_code }}
                @if($match->playerB->world_ranking)· #{{ $match->playerB->world_ranking }}@endif
            </p>
        </div>
    </div>

    {{-- Round + Date + Arrow --}}
    <div class="hidden sm:flex items-center gap-4 shrink-0">
        <div class="text-right">
            <p class="text-xs font-semibold text-white/40">{{ $match->round }}</p>
            <p class="text-[11px] text-white/25 mt-px">{{ $match->match_date->format('M d, Y') }}</p>
        </div>
        <svg class="w-4 h-4 text-white/15 group-hover:text-sport-400/60 transition-all duration-200 group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
        </svg>
    </div>

    {{-- Mobile round + date --}}
    <div class="sm:hidden flex flex-col items-end shrink-0">
        <span class="text-xs text-white/40 font-semibold">{{ $match->round }}</span>
        <span class="text-[10px] text-white/25">{{ $match->match_date->format('M d') }}</span>
    </div>
</a>
