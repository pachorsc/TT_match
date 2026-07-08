@props(['message' => 'No data available.', 'icon' => null])

<div class="card-glass px-6 py-14 sm:py-16 text-center space-y-4">
    @if($icon)
        <div class="text-gray-300 dark:text-white/15">
            {!! $icon !!}
        </div>
    @else
        <svg class="w-10 h-10 mx-auto text-gray-300 dark:text-white/15" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0-3-3m3 3 3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
        </svg>
    @endif
    <p class="text-sm text-gray-400 dark:text-white/30 max-w-xs mx-auto">{{ $message }}</p>
</div>
