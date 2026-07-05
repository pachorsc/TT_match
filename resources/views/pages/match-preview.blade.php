<x-layout :title="$match->playerA->full_name . ' vs ' . $match->playerB->full_name . ' — Match Preview'">

    <div class="space-y-10 sm:space-y-12">

        {{-- Player Matchup Header --}}
        <div class="flex flex-col sm:flex-row items-center justify-center gap-6 sm:gap-10 py-4 sm:py-6">
            <x-player-header :player="$playerA['player']" :rankingMovement="$playerA['rankingMovement']" />

            <div class="flex flex-col items-center gap-2 shrink-0">
                <div class="relative flex items-center justify-center w-12 h-12 sm:w-16 sm:h-16 rounded-full border-2 border-white/10 bg-white/[0.03]">
                    <span class="text-sm sm:text-base font-black tracking-wider text-white/40">VS</span>
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

</x-layout>
