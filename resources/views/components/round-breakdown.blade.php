@props(['playerA', 'playerB', 'breakdown', 'title'])

@php
    $categories = collect($breakdown);
    $maxTotal = $categories->flatMap(fn($d) => [$d['playerA']['total'], $d['playerB']['total']])->max();
@endphp

<div class="card-glass overflow-hidden animate-fade-slide-up">
    {{-- Header --}}
    <div class="px-5 sm:px-6 py-3.5 border-b border-black/[0.06] dark:border-white/[0.06]">
        <div class="flex items-center gap-3">
            <div class="w-1 h-4 rounded-full bg-sport-500/60"></div>
            <h3 class="section-heading">{{ $title }}</h3>
        </div>
    </div>

    {{-- Column headers --}}
    <div class="px-5 sm:px-6 pt-3 pb-2">
        <div class="grid grid-cols-[1fr_90px_90px] sm:grid-cols-[1fr_120px_120px] gap-3 text-center text-[10px] font-semibold uppercase tracking-[0.1em] text-gray-400 dark:text-white/30">
            <div class="text-left">Categoría</div>
            <div>{{ $playerA->first_name }}</div>
            <div>{{ $playerB->first_name }}</div>
        </div>
    </div>

    {{-- Rows --}}
    <div class="px-5 sm:px-6 pb-4 divide-y divide-black/[0.04] dark:divide-white/[0.04]">
        @foreach($breakdown as $category => $data)
            @php
                $aWinRate = $data['playerA']['win_rate'];
                $bWinRate = $data['playerB']['win_rate'];
                $aTotal = $data['playerA']['total'];
                $bTotal = $data['playerB']['total'];
                $isATop = $aWinRate > $bWinRate;
                $isBTop = $bWinRate > $aWinRate;
                $isTie = $aWinRate == $bWinRate;

                $aBarClass = $aWinRate >= 60 ? 'excellent' : ($aWinRate >= 45 ? 'good' : 'poor');
                $bBarClass = $bWinRate >= 60 ? 'excellent' : ($bWinRate >= 45 ? 'good' : 'poor');
            @endphp
            <div class="grid grid-cols-[1fr_90px_90px] sm:grid-cols-[1fr_120px_120px] gap-3 py-3 items-center group hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors rounded-lg -mx-2 px-2">
                {{-- Category --}}
                <div class="text-sm font-medium text-gray-500 dark:text-white/60 group-hover:text-gray-700 dark:group-hover:text-white/80 transition-colors">{{ $category }}</div>

                {{-- Player A --}}
                <div class="space-y-1.5">
                    @if($aTotal > 0)
                        <div class="flex items-center justify-center gap-1.5">
                            @if($isATop && !$isTie)
                                <svg class="w-3 h-3 text-emerald-400 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            @endif
                            <span class="font-bold text-sm {{ $isATop ? 'text-emerald-400' : 'text-gray-600 dark:text-white/70' }}">{{ $aWinRate }}%</span>
                        </div>
                        <div class="wr-bar-track">
                            <div class="wr-bar-fill {{ $aBarClass }}" style="width: {{ $aWinRate }}%"></div>
                        </div>
                        <div class="text-[10px] text-gray-400 dark:text-white/25 text-center">{{ $data['playerA']['wins'] }}-{{ $data['playerA']['losses'] }} ({{ $aTotal }})</div>
                    @else
                        <div class="text-center">
                            <span class="text-gray-300 dark:text-white/15 text-sm">—</span>
                        </div>
                    @endif
                </div>

                {{-- Player B --}}
                <div class="space-y-1.5">
                    @if($bTotal > 0)
                        <div class="flex items-center justify-center gap-1.5">
                            <span class="font-bold text-sm {{ $isBTop ? 'text-emerald-400' : 'text-gray-600 dark:text-white/70' }}">{{ $bWinRate }}%</span>
                            @if($isBTop && !$isTie)
                                <svg class="w-3 h-3 text-emerald-400 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            @endif
                        </div>
                        <div class="wr-bar-track">
                            <div class="wr-bar-fill {{ $bBarClass }}" style="width: {{ $bWinRate }}%"></div>
                        </div>
                        <div class="text-[10px] text-gray-400 dark:text-white/25 text-center">{{ $data['playerB']['wins'] }}-{{ $data['playerB']['losses'] }} ({{ $bTotal }})</div>
                    @else
                        <div class="text-center">
                            <span class="text-gray-300 dark:text-white/15 text-sm">—</span>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
