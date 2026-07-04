@props(['message' => 'No data available.', 'icon' => null])

<div class="rounded-2xl bg-gray-50 border border-gray-200 dark:bg-white/[0.03] dark:border-white/[0.06] px-6 py-12 text-center space-y-3 transition-colors duration-300">
    @if($icon)
        <div class="text-gray-300 dark:text-white/20">
            {!! $icon !!}
        </div>
    @endif
    <p class="text-sm text-gray-400 dark:text-white/30">{{ $message }}</p>
</div>
