@props(['rankings'])

<div class="card-glass overflow-hidden">
    <div class="px-5 sm:px-6 py-4 border-b border-black/[0.06] dark:border-white/[0.06]">
        <div class="flex items-center gap-3">
            <div class="w-1 h-4 rounded-full bg-sport-500/60"></div>
            <h3 class="section-heading">Ranking History</h3>
        </div>
    </div>

    @if($rankings->isEmpty())
        <x-empty-state message="No ranking data available." />
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-black/[0.06] dark:border-white/[0.06]">
                        <th class="text-left px-5 sm:px-6 py-3 text-xs font-semibold uppercase tracking-[0.1em] text-gray-400 dark:text-white/30">Date</th>
                        <th class="text-center px-5 sm:px-6 py-3 text-xs font-semibold uppercase tracking-[0.1em] text-gray-400 dark:text-white/30">Ranking</th>
                        <th class="text-right px-5 sm:px-6 py-3 text-xs font-semibold uppercase tracking-[0.1em] text-gray-400 dark:text-white/30">Points</th>
                        <th class="text-center px-5 sm:px-6 py-3 text-xs font-semibold uppercase tracking-[0.1em] text-gray-400 dark:text-white/30">Movement</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.04]">
                    @foreach($rankings as $i => $ranking)
                        @php
                            $movement = null;
                            if ($i < $rankings->count() - 1) {
                                $next = $rankings->get($i + 1);
                                $movement = $next ? $next->ranking - $ranking->ranking : null;
                            }
                        @endphp
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-5 sm:px-6 py-3 text-gray-500 dark:text-white/40">
                                {{ $ranking->ranking_date->format('M Y') }}
                            </td>
                            <td class="px-5 sm:px-6 py-3 text-center font-bold text-gray-900 dark:text-white/90">
                                #{{ $ranking->ranking }}
                            </td>
                            <td class="px-5 sm:px-6 py-3 text-right font-mono text-gray-600 dark:text-white/70">
                                {{ number_format($ranking->rating_points) }}
                            </td>
                            <td class="px-5 sm:px-6 py-3 text-center">
                                @if($movement === null)
                                    <span class="text-gray-300 dark:text-white/20">—</span>
                                @elseif($movement > 0)
                                    <span class="text-emerald-400 font-semibold">↑{{ $movement }}</span>
                                @elseif($movement < 0)
                                    <span class="text-red-400 font-semibold">↓{{ abs($movement) }}</span>
                                @else
                                    <span class="text-gray-400 dark:text-white/30">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
