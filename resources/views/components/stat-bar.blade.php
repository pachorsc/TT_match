@props(['valueA', 'valueB', 'labelA' => '', 'labelB' => '', 'sampleA' => null, 'sampleB' => null, 'colorA' => 'bg-sport-500/60', 'colorB' => 'bg-sport-500/30'])

@php
    $total = $valueA + $valueB;
    $pctA = $total > 0 ? round(($valueA / $total) * 100) : 50;
    $pctB = $total > 0 ? 100 - $pctA : 50;
    $isATop = $valueA > $valueB;
    $isBTop = $valueB > $valueA;
@endphp

<div class="space-y-2">
    <div class="flex items-center justify-between text-sm">
        <span class="font-bold {{ $isATop ? 'text-emerald-400' : 'text-white/70' }}">{{ $labelA }}</span>
        <span class="font-bold {{ $isBTop ? 'text-emerald-400' : 'text-white/70' }}">{{ $labelB }}</span>
    </div>

    <div class="flex items-center gap-0 h-2 rounded-full overflow-hidden bg-white/[0.04]">
        <div class="h-full transition-all duration-500 {{ $pctA > 0 ? $colorA : '' }}" style="width: {{ $pctA }}%"></div>
        <div class="h-full transition-all duration-500 {{ $pctB > 0 ? $colorB : '' }}" style="width: {{ $pctB }}%"></div>
    </div>

    <div class="flex items-center justify-between text-[10px] text-white/30">
        <span>{{ $sampleA !== null ? $sampleA . ' partidos' : '' }}</span>
        <span>{{ $sampleB !== null ? $sampleB . ' partidos' : '' }}</span>
    </div>
</div>
