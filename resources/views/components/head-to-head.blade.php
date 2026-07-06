@props(['playerA', 'playerB', 'headToHead'])

<div class="card-glass overflow-hidden">
    <div class="px-5 sm:px-6 py-4 border-b border-white/[0.06]">
        <div class="flex items-center gap-3">
            <div class="w-1 h-4 rounded-full bg-sport-500/60"></div>
            <h3 class="section-heading">Head to Head</h3>
        </div>
    </div>

    @if($headToHead['total_matches'] === 0)
        <x-empty-state message="No head-to-head matches found in the last 2 years." />
    @else
        <div class="px-5 sm:px-6 py-6 sm:py-8">
            <div class="flex items-center justify-center gap-6 sm:gap-12 mb-6">
                <div class="text-center flex-1">
                    <p class="text-3xl sm:text-4xl font-black {{ $headToHead['player_a_wins'] > $headToHead['player_b_wins'] ? 'text-emerald-400' : 'text-white/80' }}">
                        {{ $headToHead['player_a_wins'] }}
                    </p>
                    <p class="text-xs text-white/40 uppercase tracking-wider mt-1">{{ $playerA->full_name }}</p>
                </div>

                <div class="text-center shrink-0">
                    <p class="text-xl font-bold text-white/30">{{ $headToHead['total_matches'] }}</p>
                    <p class="text-[10px] text-white/20 uppercase tracking-widest">Matches</p>
                </div>

                <div class="text-center flex-1">
                    <p class="text-3xl sm:text-4xl font-black {{ $headToHead['player_b_wins'] > $headToHead['player_a_wins'] ? 'text-emerald-400' : 'text-white/80' }}">
                        {{ $headToHead['player_b_wins'] }}
                    </p>
                    <p class="text-xs text-white/40 uppercase tracking-wider mt-1">{{ $playerB->full_name }}</p>
                </div>
            </div>

            {{-- Win rate progress bar --}}
            @if($headToHead['total_matches'] > 0)
                @php
                    $aPct = round(($headToHead['player_a_wins'] / $headToHead['total_matches']) * 100);
                    $bPct = round(($headToHead['player_b_wins'] / $headToHead['total_matches']) * 100);
                @endphp
                <div class="flex items-center gap-0 h-2 rounded-full overflow-hidden bg-white/[0.04] max-w-sm mx-auto">
                    <div class="h-full transition-all duration-500 {{ $aPct > 0 ? 'bg-sport-500/60' : '' }}" style="width: {{ $aPct }}%"></div>
                    <div class="h-full transition-all duration-500 {{ $bPct > 0 ? 'bg-sport-500/30' : '' }}" style="width: {{ $bPct }}%"></div>
                </div>
                <div class="flex items-center justify-between max-w-sm mx-auto mt-1.5">
                    <span class="text-[10px] text-white/30 font-semibold">{{ $aPct }}%</span>
                    <span class="text-[10px] text-white/20">last 2 years</span>
                    <span class="text-[10px] text-white/30 font-semibold">{{ $bPct }}%</span>
                </div>
            @endif
        </div>

        @if($headToHead['matches']->isNotEmpty())
            <div class="overflow-x-auto border-t border-white/[0.06]">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-white/[0.06]">
                            <th class="text-left px-5 sm:px-6 py-3 text-xs font-semibold uppercase tracking-[0.1em] text-white/30">Date</th>
                            <th class="text-left px-5 sm:px-6 py-3 text-xs font-semibold uppercase tracking-[0.1em] text-white/30">Tournament</th>
                            <th class="text-center px-5 sm:px-6 py-3 text-xs font-semibold uppercase tracking-[0.1em] text-white/30">Score</th>
                            <th class="text-center px-5 sm:px-6 py-3 text-xs font-semibold uppercase tracking-[0.1em] text-white/30">Winner</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04]">
                        @foreach($headToHead['matches'] as $match)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 sm:px-6 py-3 text-white/40">
                                    {{ $match->match_date->month === 1 && $match->match_date->day === 1 ? $match->match_date->format('Y') : $match->match_date->format('d M Y') }}
                                </td>
                                <td class="px-5 sm:px-6 py-3 text-white/80">
                                    {{ $match->tournament->name }}
                                </td>
                                <td class="px-5 sm:px-6 py-3 text-center font-mono font-bold text-white/80">
                                    {{ $match->player_a_sets }} – {{ $match->player_b_sets }}
                                </td>
                                <td class="px-5 sm:px-6 py-3 text-center">
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
