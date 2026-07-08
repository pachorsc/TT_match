@props(['icon' => 'chart-bar', 'text', 'highlight' => null])

@php
    $icons = [
        'chart-bar' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>',
        'trophy' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.996.178-1.792.882-2.181 1.827a5.972 5.972 0 00.372 3.938M15.75 4.236c.996.178 1.792.882 2.181 1.827a5.972 5.972 0 01-.372 3.938m-7.5 0V4.236c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v.514M12 2.25l2.25.75m0 0l3 2.25m-3-2.25l-3 2.25m3-2.25l3-2.25m-3 2.25l-3-2.25"/>',
        'trending-up' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/>',
        'check-badge' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    ];
@endphp

<div class="insight-card">
    <div class="w-8 h-8 rounded-lg bg-sport-500/10 border border-sport-500/20 flex items-center justify-center shrink-0">
        <svg class="w-4 h-4 text-sport-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            {!! $icons[$icon] ?? $icons['chart-bar'] !!}
        </svg>
    </div>
    <p class="text-sm text-white/60 leading-relaxed">
        {!! $highlight ? '<span class="font-bold text-white/90">' . e($highlight) . '</span> ' : '' !!}
        {{ $text }}
    </p>
</div>
