@props(['player', 'matches'])

<div class="rounded-2xl bg-white/[0.03] border border-white/[0.06] overflow-hidden">
    <div class="px-6 py-4 border-b border-white/[0.06]">
        <h3 class="text-sm font-bold uppercase tracking-wider text-white/60">Last 7 Matches — {{ $player->full_name }}</h3>
    </div>

    @if($matches->isEmpty())
        <div class="px-6 py-10 text-center text-white/30 text-sm">
            No completed matches found.
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/[0.06]">
                        <th class="text-left px-6 py-3 text-xs font-semibold uppercase tracking-wider text-white/40">Tournament</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold uppercase tracking-wider text-white/40">Opponent</th>
                        <th class="text-center px-6 py-3 text-xs font-semibold uppercase tracking-wider text-white/40">Score</th>
                        <th class="text-center px-6 py-3 text-xs font-semibold uppercase tracking-wider text-white/40">Result</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.04]">
                    @foreach($matches as $match)
                        @php
                            $isPlayerA = $match->player_a_id === $player->id;
                            $opponent = $isPlayerA ? $match->playerB : $match->playerA;
                            $won = $match->winner_id === $player->id;
                            $playerSets = $isPlayerA ? $match->player_a_sets : $match->player_b_sets;
                            $opponentSets = $isPlayerA ? $match->player_b_sets : $match->player_a_sets;
                        @endphp
                        <tr class="hover:bg-white/[0.02] transition-colors">
                            <td class="px-6 py-3">
                                <span class="text-white/50">{{ $match->tournament->name }}</span>
                            </td>
                            <td class="px-6 py-3 font-medium">
                                {{ $opponent->full_name }}
                            </td>
                            <td class="px-6 py-3 text-center font-mono font-bold">
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
