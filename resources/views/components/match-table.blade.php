@props(['player', 'matches'])

<div class="rounded-2xl bg-gray-50 border border-gray-200 dark:bg-white/[0.03] dark:border-white/[0.06] overflow-hidden transition-colors duration-300">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-white/[0.06]">
        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-white/60">Last 7 Matches — {{ $player->full_name }}</h3>
    </div>

    @if($matches->isEmpty())
        <div class="px-6 py-10 text-center text-gray-400 dark:text-white/30 text-sm">
            No completed matches found.
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-white/[0.06]">
                        <th class="text-left px-6 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/40">Tournament</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/40">Opponent</th>
                        <th class="text-center px-6 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/40">Score</th>
                        <th class="text-center px-6 py-3 text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/40">Result</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/[0.04]">
                    @foreach($matches as $match)
                        @php
                            $isPlayerA = $match->player_a_id === $player->id;
                            $opponent = $isPlayerA ? $match->playerB : $match->playerA;
                            $won = $match->winner_id === $player->id;
                            $playerSets = $isPlayerA ? $match->player_a_sets : $match->player_b_sets;
                            $opponentSets = $isPlayerA ? $match->player_b_sets : $match->player_a_sets;
                        @endphp
                        <tr class="hover:bg-gray-100 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-6 py-3">
                                <span class="text-gray-500 dark:text-white/50">{{ $match->tournament->name }}</span>
                            </td>
                            <td class="px-6 py-3 font-medium text-gray-900 dark:text-white">
                                {{ $opponent->full_name }}
                            </td>
                            <td class="px-6 py-3 text-center font-mono font-bold text-gray-900 dark:text-white">
                                {{ $playerSets }} – {{ $opponentSets }}
                            </td>
                            <td class="px-6 py-3 text-center">
                                <x-badge :won="$won" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
