@props(['player', 'matches', 'availableYears', 'tournaments', 'selectedYear' => null, 'selectedTournamentId' => null])

<div class="card-glass overflow-hidden">
    <div class="px-5 sm:px-6 py-4 border-b border-black/[0.06] dark:border-white/[0.06]">
        <div class="flex items-center gap-3">
            <div class="w-1 h-4 rounded-full bg-sport-500/60"></div>
            <h3 class="section-heading">Match History</h3>
        </div>
    </div>

    {{-- Filters --}}
    <div class="px-5 sm:px-6 py-3 border-b border-black/[0.06] dark:border-white/[0.06] bg-black/[0.01] dark:bg-white/[0.01]">
        <form method="GET" action="{{ route('players.show', $player) }}" class="flex flex-wrap items-center gap-3">
            <select name="year" onchange="this.form.submit()" class="bg-black/[0.04] dark:bg-white/[0.04] border border-black/[0.08] dark:border-white/[0.08] rounded-xl px-3 py-2 text-xs font-semibold text-gray-500 dark:text-white/60 outline-none transition-all duration-200 focus:border-sport-500/40 focus:bg-black/[0.06] dark:focus:bg-white/[0.06]">
                <option value="">All Years</option>
                @foreach($availableYears as $year)
                    <option value="{{ $year }}" {{ (int) $selectedYear === $year ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>

            <select name="tournament_id" onchange="this.form.submit()" class="bg-black/[0.04] dark:bg-white/[0.04] border border-black/[0.08] dark:border-white/[0.08] rounded-xl px-3 py-2 text-xs font-semibold text-gray-500 dark:text-white/60 outline-none transition-all duration-200 focus:border-sport-500/40 focus:bg-black/[0.06] dark:focus:bg-white/[0.06]">
                <option value="">All Tournaments</option>
                @foreach($tournaments as $tournament)
                    <option value="{{ $tournament['id'] }}" {{ (int) $selectedTournamentId === $tournament['id'] ? 'selected' : '' }}>{{ $tournament['name'] }}</option>
                @endforeach
            </select>

            @if($selectedYear || $selectedTournamentId)
                <a href="{{ route('players.show', $player) }}" class="text-xs text-gray-400 dark:text-white/40 hover:text-gray-600 dark:hover:text-white/70 transition-colors underline underline-offset-2">Clear</a>
            @endif
        </form>
    </div>

    @if($matches->isEmpty())
        <x-empty-state message="No completed matches found." />
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-black/[0.06] dark:border-white/[0.06]">
                        <th class="text-left px-5 sm:px-6 py-3 text-xs font-semibold uppercase tracking-[0.1em] text-gray-400 dark:text-white/30">Date</th>
                        <th class="text-left px-5 sm:px-6 py-3 text-xs font-semibold uppercase tracking-[0.1em] text-gray-400 dark:text-white/30">Tournament</th>
                        <th class="text-left px-5 sm:px-6 py-3 text-xs font-semibold uppercase tracking-[0.1em] text-gray-400 dark:text-white/30">Opponent</th>
                        <th class="text-center px-5 sm:px-6 py-3 text-xs font-semibold uppercase tracking-[0.1em] text-gray-400 dark:text-white/30">Score</th>
                        <th class="text-center px-5 sm:px-6 py-3 text-xs font-semibold uppercase tracking-[0.1em] text-gray-400 dark:text-white/30">Result</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.04]">
                    @foreach($matches as $match)
                        @php
                            $isPlayerA = $match->player_a_id === $player->id;
                            $opponent = $isPlayerA ? $match->playerB : $match->playerA;
                            $won = $match->winner_id === $player->id;
                            $playerSets = $isPlayerA ? $match->player_a_sets : $match->player_b_sets;
                            $opponentSets = $isPlayerA ? $match->player_b_sets : $match->player_a_sets;
                        @endphp
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-5 sm:px-6 py-3 text-gray-500 dark:text-white/40 whitespace-nowrap">
                                {{ $match->match_date->format('Y') }}
                            </td>
                            <td class="px-5 sm:px-6 py-3 text-gray-500 dark:text-white/60 max-w-[200px] truncate">
                                {{ $match->tournament->name }}
                            </td>
                            <td class="px-5 sm:px-6 py-3 font-medium text-gray-700 dark:text-white/80">
                                <a href="{{ route('players.show', $opponent) }}" class="hover:text-sport-400 transition-colors">
                                    {{ $opponent->full_name }}
                                </a>
                            </td>
                            <td class="px-5 sm:px-6 py-3 text-center font-mono font-bold text-gray-700 dark:text-white/80">
                                <a href="{{ route('matches.show', $match) }}" class="hover:text-sport-400 transition-colors">
                                    {{ $playerSets }} – {{ $opponentSets }}
                                </a>
                            </td>
                            <td class="px-5 sm:px-6 py-3 text-center">
                                <x-badge :won="$won" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-5 sm:px-6 py-4 border-t border-black/[0.06] dark:border-white/[0.06]">
            {{ $matches->appends(request()->only(['year', 'tournament_id']))->links() }}
        </div>
    @endif
</div>
