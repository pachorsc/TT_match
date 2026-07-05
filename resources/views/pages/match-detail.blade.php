<x-layout :title="$playerA->full_name . ' vs ' . $playerB->full_name . ' — Match Detail'">

    <div class="space-y-10 sm:space-y-12">

        {{-- Player Matchup Header --}}
        <div class="flex flex-col sm:flex-row items-center justify-center gap-6 sm:gap-10 py-4 sm:py-6">
            <x-player-header :player="$playerA" />
            <div class="relative flex items-center justify-center w-12 h-12 sm:w-16 sm:h-16 rounded-full border-2 border-white/10 bg-white/[0.03]">
                <span class="text-sm sm:text-base font-black tracking-wider text-white/40">VS</span>
            </div>
            <x-player-header :player="$playerB" />
        </div>

        {{-- Match Header --}}
        <x-match-header :tournament="$tournament" :match="$match" />

        {{-- Final Result --}}
        <div class="max-w-lg mx-auto">
            <div class="card-glass p-6 sm:p-8 text-center">
                <div class="flex items-center justify-center gap-6 sm:gap-10">

                    {{-- Player A --}}
                    <div class="text-right min-w-0 flex-1 {{ $winner && $winner->id === $playerA->id ? '' : 'opacity-40' }}">
                        <p class="text-base sm:text-lg font-bold truncate text-white/90">
                            {{ $playerA->first_name }} {{ $playerA->last_name }}
                        </p>
                        <p class="text-xs text-white/30 mt-0.5">{{ $playerA->country_code }} @if($playerA->world_ranking)· #{{ $playerA->world_ranking }}@endif</p>
                    </div>

                    {{-- Score --}}
                    <div class="flex flex-col items-center shrink-0">
                        <div class="flex items-baseline gap-3">
                            <span class="text-3xl sm:text-4xl font-black {{ $winner && $winner->id === $playerA->id ? 'text-emerald-400' : 'text-white/50' }}">
                                {{ $match->player_a_sets }}
                            </span>
                            <span class="text-lg font-bold text-white/20">—</span>
                            <span class="text-3xl sm:text-4xl font-black {{ $winner && $winner->id === $playerB->id ? 'text-emerald-400' : 'text-white/50' }}">
                                {{ $match->player_b_sets }}
                            </span>
                        </div>
                        @if($winner)
                            <div class="mt-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-500/30">
                                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                                <span class="text-xs font-bold text-emerald-400">{{ $winner->first_name }} wins</span>
                            </div>
                        @endif
                    </div>

                    {{-- Player B --}}
                    <div class="text-left min-w-0 flex-1 {{ $winner && $winner->id === $playerB->id ? '' : 'opacity-40' }}">
                        <p class="text-base sm:text-lg font-bold truncate text-white/90">
                            {{ $playerB->first_name }} {{ $playerB->last_name }}
                        </p>
                        <p class="text-xs text-white/30 mt-0.5">{{ $playerB->country_code }} @if($playerB->world_ranking)· #{{ $playerB->world_ranking }}@endif</p>
                    </div>

                </div>
            </div>
        </div>

        {{-- Set-by-Set Breakdown --}}
        <div class="space-y-5">
            <div class="flex items-center gap-3">
                <div class="w-1 h-4 rounded-full bg-sport-500/60"></div>
                <h2 class="section-heading">Set-by-Set Breakdown</h2>
            </div>
            <x-set-breakdown :sets="$sets" :playerA="$playerA" :playerB="$playerB" />
        </div>

    </div>

</x-layout>
