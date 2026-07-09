<x-layout :title="$match->playerA->full_name . ' vs ' . $match->playerB->full_name . ' — Match Preview'">

    <div class="space-y-10 sm:space-y-12">

        {{-- Player Matchup Header --}}
        <div class="flex flex-col sm:flex-row items-center justify-center gap-6 sm:gap-10 py-4 sm:py-6">
            <x-player-header :player="$playerA['player']" :rankingMovement="$playerA['rankingMovement']" />

            <div class="flex flex-col items-center gap-2 shrink-0">
                <div class="relative flex items-center justify-center w-12 h-12 sm:w-16 sm:h-16 rounded-full border-2 border-gray-200/80 dark:border-white/10 bg-gray-100/50 dark:bg-white/[0.03]">
                    <span class="text-sm sm:text-base font-black tracking-wider text-gray-500 dark:text-white/40">VS</span>
                </div>
            </div>

            <x-player-header :player="$playerB['player']" :rankingMovement="$playerB['rankingMovement']" />
        </div>

        {{-- Match Header --}}
        <x-match-header :tournament="$tournament" :match="$match" />

        {{-- Player Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
            <x-player-card :player="$playerA['player']" :stats="$playerA['stats']" />
            <x-player-card :player="$playerB['player']" :stats="$playerB['stats']" />
        </div>

        {{-- Last 7 Matches --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
            <x-match-table :player="$playerA['player']" :matches="$playerA['last7']" />
            <x-match-table :player="$playerB['player']" :matches="$playerB['last7']" />
        </div>

        {{-- Head to Head --}}
        <x-head-to-head
            :playerA="$playerA['player']"
            :playerB="$playerB['player']"
            :headToHead="$headToHead"
        />

        {{-- Latest Videos --}}
        <div class="space-y-5">
            <div class="flex items-center gap-3">
                <div class="w-1 h-4 rounded-full bg-sport-500/60"></div>
                <h2 class="section-heading">Latest Videos</h2>
            </div>

            <div class="videos-container"
                 data-players='{{ json_encode([$playerA["player"]->id, $playerB["player"]->id]) }}'
                 data-player-names='{{ json_encode([$playerA["player"]->id => $playerA["player"]->full_name, $playerB["player"]->id => $playerB["player"]->full_name]) }}'>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    @foreach([$playerA, $playerB] as $pData)
                        <div class="videos-player-slot" data-player-id="{{ $pData['player']->id }}">
                            <div class="flex items-center gap-3 mb-4">
                                <span class="text-sm font-semibold text-gray-500 dark:text-white/60">{{ $pData['player']->full_name }}</span>
                                <span class="text-xs text-gray-300 dark:text-white/20">—</span>
                                <span class="text-xs text-gray-400 dark:text-white/30">YouTube</span>
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
                                    <svg class="w-10 h-10 mx-auto text-gray-300 dark:text-white/15" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z" />
                                    </svg>
                                    <p class="text-sm text-gray-400 dark:text-white/30 max-w-xs mx-auto">No videos found for this player.</p>
                                </div>
                            </div>
                            <div class="videos-error hidden">
                                <div class="card-glass px-6 py-14 sm:py-16 text-center space-y-4">
                                    <svg class="w-10 h-10 mx-auto text-gray-300 dark:text-white/15" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                    </svg>
                                    <p class="text-sm text-gray-400 dark:text-white/30 max-w-xs mx-auto">Error loading videos. Try again.</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

</x-layout>
