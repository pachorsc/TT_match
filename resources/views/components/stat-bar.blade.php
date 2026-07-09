@props(['valueA', 'valueB', 'labelA' => '', 'labelB' => '', 'sampleA' => null, 'sampleB' => null, 'highlight' => false])

@php
    $total = $valueA + $valueB;
    $pctA = $total > 0 ? round(($valueA / $total) * 100) : 50;
    $pctB = $total > 0 ? 100 - $pctA : 50;
    $isATop = $valueA > $valueB;
    $isBTop = $valueB > $valueA;
    $isTie = $valueA == $valueB;
@endphp

<div class="space-y-2.5">
    {{-- Labels --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            @if($isATop && !$isTie)
                <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2v20M2 12l4-4 4 4"/>
                </svg>
            @endif
            <span class="text-sm font-bold {{ $isATop ? 'text-emerald-400' : 'text-gray-500 dark:text-white/60' }}">{{ $labelA }}</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-sm font-bold {{ $isBTop ? 'text-emerald-400' : 'text-gray-500 dark:text-white/60' }}">{{ $labelB }}</span>
            @if($isBTop && !$isTie)
                <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2v20M2 12l4-4 4 4"/>
                </svg>
            @endif
        </div>
    </div>

    {{-- Bar --}}
    <div class="stat-bar-track">
        <div class="stat-bar-fill-a {{ $isATop ? 'is-winner' : '' }}" style="width: {{ $pctA }}%"></div>
        <div class="stat-bar-fill-b {{ $isBTop ? 'is-winner' : '' }} absolute top-0 right-0" style="width: {{ $pctB }}%"></div>
            @if($highlight && !$isTie)
                <div class="absolute top-0 bottom-0 w-px bg-gray-400/60 dark:bg-white/20" style="left: 50%"></div>
            @endif
    </div>

    {{-- Sample sizes --}}
    <div class="flex items-center justify-between text-[10px] text-gray-400 dark:text-white/25">
        <span>{{ $sampleA !== null ? $sampleA . ' partidos' : '' }}</span>
        <span>{{ $sampleB !== null ? $sampleB . ' partidos' : '' }}</span>
    </div>
</div>
