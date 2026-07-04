@props(['player'])

<div class="rounded-2xl bg-white/[0.03] border border-white/[0.06] p-6 space-y-4">
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-bold tracking-tight">{{ $player->full_name }}</h3>
        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-white/10 text-white/70 border border-white/10">
            {{ $player->country_code }}
        </span>
    </div>

    <div class="grid grid-cols-2 gap-3 text-sm">
        <div class="space-y-1">
            <span class="text-white/40 text-xs uppercase tracking-wider">Ranking</span>
            <p class="font-bold text-lg">{{ $player->world_ranking ? '#' . $player->world_ranking : '—' }}</p>
        </div>

        <div class="space-y-1">
            <span class="text-white/40 text-xs uppercase tracking-wider">Rating</span>
            <p class="font-bold text-lg">{{ $player->rating_points ? number_format($player->rating_points) : '—' }}</p>
        </div>

        <div class="space-y-1">
            <span class="text-white/40 text-xs uppercase tracking-wider">Hand</span>
            <p class="font-semibold">{{ $player->dominant_hand }}</p>
        </div>

        <div class="space-y-1">
            <span class="text-white/40 text-xs uppercase tracking-wider">Style</span>
            <p class="font-semibold">{{ $player->playing_style ?? '—' }}</p>
        </div>

        <div class="space-y-1">
            <span class="text-white/40 text-xs uppercase tracking-wider">Age</span>
            <p class="font-semibold">{{ $player->date_of_birth->age }} yrs</p>
        </div>

        <div class="space-y-1">
            <span class="text-white/40 text-xs uppercase tracking-wider">Height</span>
            <p class="font-semibold">{{ $player->height_cm ? $player->height_cm . ' cm' : '—' }}</p>
        </div>
    </div>
</div>
