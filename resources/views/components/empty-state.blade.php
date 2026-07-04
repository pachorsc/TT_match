@props(['message' => 'No data available.', 'icon' => null])

<div class="rounded-2xl bg-white/[0.03] border border-white/[0.06] px-6 py-12 text-center space-y-3">
    @if($icon)
        <div class="text-white/20">
            {!! $icon !!}
        </div>
    @endif
    <p class="text-sm text-white/30">{{ $message }}</p>
</div>
