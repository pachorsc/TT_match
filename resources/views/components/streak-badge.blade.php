@props(['streak'])

@if($streak && $streak['count'] > 0)
    @if($streak['type'] === 'W')
        <span class="streak-badge win">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            <span>{{ $streak['count'] }}W</span>
        </span>
    @elseif($streak['type'] === 'L')
        <span class="streak-badge loss">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 6L6 18M6 6l12 12"/>
            </svg>
            <span>{{ $streak['count'] }}L</span>
        </span>
    @endif
@endif
