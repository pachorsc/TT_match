@props(['playerA', 'playerB', 'headToHead'])

<div class="rounded-2xl bg-white/[0.03] border border-white/[0.06] overflow-hidden">
    <div class="px-6 py-4 border-b border-white/[0.06]">
        <h3 class="text-sm font-bold uppercase tracking-wider text-white/60">Head to Head</h3>
    </div>

    @if($headToHead['total_matches'] === 0)
        <div class="px-6 py-10 text-center text-white/30 text-sm">
            No head-to-head matches found in the last 2 years.
        </div>
    @else
        <div class="px-6 py-6">
            <div class="flex items-center justify-center gap-8 mb-6">
                <div class="text-center">
                    <p class="text-3xl font-bold text-white">{{ $headToHead['player_a_wins'] }}</p>
                    <p class="text-xs text-white/40 uppercase tracking-wider mt-1">{{ $playerA->full_name }}</p>
                </div>

                <div class="text-center">
                    <p class="text-sm text-white/30 mb-1">{{ $headToHead['total_matches'] }} matches</p>
                    <p class="text-xs text-white/20">last 2 years</p>
                </div>

                <div class="text-center">
                    <p class="text-3xl font-bold text-white">{{ $headToHead['player_b_wins'] }}</p>
                    <p class="text-xs text-white/40 uppercase tracking-wider mt-1">{{ $playerB->full_name }}</p>
                </div>
            </div>
        </div>

        @if($headToHead['matches']->isNotEmpty())
            <div class="overflow-x-auto border-t border-white/[0.06]">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-white/[0.06]">
                            <th class="text-left px-6 py-3 text-xs font-semibold uppercase tracking-wider text-white/40">Date</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold uppercase tracking-wider text-white/40">Tournament</th>
                            <th class="text-center px-6 py-3 text-xs font-semibold uppercase tracking-wider text-white/40">Score</th>
                            <th class="text-center px-6 py-3 text-xs font-semibold uppercase tracking-wider text-white/40">Winner</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04]">
                        @foreach($headToHead['matches'] as $match)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-6 py-3 text-white/50">
                                    {{ $match->match_date->format('d M Y') }}
                                </td>
                                <td class="px-6 py-3">
                                    {{ $match->tournament->name }}
                                </td>
                                <td class="px-6 py-3 text-center font-mono font-bold">
                                    {{ $match->player_a_sets }} – {{ $match->player_b_sets }}
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <x-badge :won="true" :label="$match->winner->full_name" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endif
</div>
