<x-layout :title="$match->playerA->full_name . ' vs ' . $match->playerB->full_name . ' — Match Preview'">

    <div class="space-y-8">

        {{-- Player Matchup Header --}}
        <div class="flex items-center justify-between">
            <x-player-header :player="$playerA['player']" :rankingMovement="$playerA['rankingMovement']" />
            <span class="text-gray-300 dark:text-white/20 text-sm font-semibold uppercase tracking-widest">vs</span>
            <x-player-header :player="$playerB['player']" :rankingMovement="$playerB['rankingMovement']" />
        </div>

        {{-- Match Header --}}
        <x-match-header :tournament="$tournament" :match="$match" />

        {{-- Player Cards --}}
        <div class="grid grid-cols-2 gap-6">
            <x-player-card :player="$playerA['player']" :stats="$playerA['stats']" />
            <x-player-card :player="$playerB['player']" :stats="$playerB['stats']" />
        </div>

        {{-- Last 7 Matches --}}
        <div class="grid grid-cols-2 gap-6">
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
        <div class="space-y-4">
            <h2 class="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-white/60">Latest News</h2>

            @if($news->isEmpty())
                <x-empty-state message="No recent news available." />
            @else
                <div class="grid grid-cols-3 gap-4">
                    @foreach($news as $item)
                        <x-news-card :news="$item" />
                    @endforeach
                </div>
            @endif
        </div>

    </div>

</x-layout>
