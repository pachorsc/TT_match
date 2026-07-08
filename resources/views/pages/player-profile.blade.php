<x-layout :title="$player->full_name . ' — Profile'">

    <div class="space-y-10 sm:space-y-12">

        {{-- Player Profile Header --}}
        <x-player-profile-header :player="$player" />

        {{-- Performance & Ranking History --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            <x-performance-breakdown :stats="$stats" :streak="$streak" />
            <x-ranking-history :rankings="$rankingHistory" />
        </div>

        {{-- Match History --}}
        <x-match-history-table
            :player="$player"
            :matches="$matches"
            :availableYears="$availableYears"
            :tournaments="$tournaments"
            :selectedYear="$selectedYear"
            :selectedTournamentId="$selectedTournamentId"
        />

    </div>

</x-layout>
