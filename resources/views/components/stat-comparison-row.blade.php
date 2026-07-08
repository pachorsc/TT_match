@props(['label', 'valueA', 'valueB', 'sampleA' => null, 'sampleB' => null, 'inverse' => false])

@php
    $isATop = $inverse ? $valueA < $valueB : $valueA > $valueB;
    $isBTop = $inverse ? $valueB < $valueA : $valueB > $valueA;
    $isTie = $valueA == $valueB;
@endphp

<div class="flex items-center justify-between py-3 border-b border-black/[0.04] dark:border-white/[0.04] last:border-0">
    <div class="flex-1 text-left">
        <span class="font-bold {{ $isATop ? 'text-emerald-400' : 'text-gray-600 dark:text-white/70' }}">{{ $valueA }}</span>
        @if($sampleA !== null)
            <span class="text-[10px] text-gray-300 dark:text-white/20 ml-1">({{ $sampleA }})</span>
        @endif
    </div>

    <div class="px-4 text-center shrink-0">
        <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-white/40">{{ $label }}</span>
    </div>

    <div class="flex-1 text-right">
        @if($sampleB !== null)
            <span class="text-[10px] text-gray-300 dark:text-white/20 mr-1">({{ $sampleB }})</span>
        @endif
        <span class="font-bold {{ $isBTop ? 'text-emerald-400' : 'text-gray-600 dark:text-white/70' }}">{{ $valueB }}</span>
    </div>
</div>
