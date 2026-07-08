@props(['playerA', 'playerB', 'breakdown', 'title'])

<div class="card-glass overflow-hidden">
    <div class="px-5 sm:px-6 py-4 border-b border-white/[0.06]">
        <div class="flex items-center gap-3">
            <div class="w-1 h-4 rounded-full bg-sport-500/60"></div>
            <h3 class="section-heading">{{ $title }}</h3>
        </div>
    </div>

    <div class="px-5 sm:px-6 py-4">
        {{-- Header row --}}
        <div class="grid grid-cols-[1fr_80px_80px] sm:grid-cols-[1fr_100px_100px] gap-2 text-center text-[10px] font-semibold uppercase tracking-[0.1em] text-white/30 pb-2 border-b border-white/[0.06]">
            <div class="text-left">Categoría</div>
            <div>{{ $playerA->first_name }}</div>
            <div>{{ $playerB->first_name }}</div>
        </div>

        {{-- Data rows --}}
        <div class="divide-y divide-white/[0.04]">
            @foreach($breakdown as $category => $data)
                @php
                    $aWinRate = $data['playerA']['win_rate'];
                    $bWinRate = $data['playerB']['win_rate'];
                    $aTotal = $data['playerA']['total'];
                    $bTotal = $data['playerB']['total'];
                    $isATop = $aWinRate > $bWinRate;
                    $isBTop = $bWinRate > $aWinRate;
                @endphp
                <div class="grid grid-cols-[1fr_80px_80px] sm:grid-cols-[1fr_100px_100px] gap-2 py-3 items-center">
                    <div class="text-sm font-medium text-white/60">{{ $category }}</div>
                    <div class="text-center">
                        @if($aTotal > 0)
                            <span class="font-bold text-sm {{ $isATop ? 'text-emerald-400' : 'text-white/70' }}">{{ $aWinRate }}%</span>
                            <span class="block text-[10px] text-white/25">{{ $data['playerA']['wins'] }}-{{ $data['playerA']['losses'] }} ({{ $aTotal }})</span>
                        @else
                            <span class="text-white/20">—</span>
                        @endif
                    </div>
                    <div class="text-center">
                        @if($bTotal > 0)
                            <span class="font-bold text-sm {{ $isBTop ? 'text-emerald-400' : 'text-white/70' }}">{{ $bWinRate }}%</span>
                            <span class="block text-[10px] text-white/25">{{ $data['playerB']['wins'] }}-{{ $data['playerB']['losses'] }} ({{ $bTotal }})</span>
                        @else
                            <span class="text-white/20">—</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
