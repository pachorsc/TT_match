@props(['player', 'matches'])

<div class="card-glass overflow-hidden">
    <div class="px-5 sm:px-6 py-4 border-b border-white/[0.06]">
        <div class="flex items-center gap-3">
            <div class="w-1 h-4 rounded-full bg-sport-500/60"></div>
            <h3 class="section-heading">Last 7 Matches — {{ $player->full_name }}</h3>
        </div>
    </div>

    @if($matches->isEmpty())
        <x-empty-state message="No completed matches found." />
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/[0.06]">
                        <th class="text-left px-5 sm:px-6 py-3 text-xs font-semibold uppercase tracking-[0.1em] text-white/30">Tournament</th>
                        <th class="text-left px-5 sm:px-6 py-3 text-xs font-semibold uppercase tracking-[0.1em] text-white/30">Opponent</th>
                        <th class="text-center px-5 sm:px-6 py-3 text-xs font-semibold uppercase tracking-[0.1em] text-white/30">Score</th>
                        <th class="text-center px-5 sm:px-6 py-3 text-xs font-semibold uppercase tracking-[0.1em] text-white/30">Result</th>
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
                            <td class="px-5 sm:px-6 py-3 text-white/40">
                                {{ $match->tournament->name }}
                            </td>
                            <td class="px-5 sm:px-6 py-3 font-medium">
                                <a href="{{ route('players.show', $opponent) }}" class="text-white/80 hover:text-sport-400 transition-colors">{{ $opponent->full_name }}</a>
                            </td>
                            <td class="px-5 sm:px-6 py-3 text-center font-mono font-bold text-white/80">
                                {{ $playerSets }} – {{ $opponentSets }}
                            </td>
                            <td class="px-5 sm:px-6 py-3 text-center">
                                <x-badge :won="$won" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
