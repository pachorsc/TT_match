@props(['news'])

<div class="card-glass p-5 space-y-3 cursor-default">
    <div class="flex items-center justify-between gap-3">
        <span class="text-xs font-semibold text-white/30 uppercase tracking-wider">{{ $news->source }}</span>
        <span class="text-xs text-white/20 whitespace-nowrap">{{ $news->published_at->diffForHumans() }}</span>
    </div>

    <h4 class="font-bold text-sm leading-snug line-clamp-2 text-white/90">{{ $news->headline }}</h4>

    <p class="text-xs text-white/50 leading-relaxed line-clamp-3">{{ $news->summary }}</p>

    @if($news->player)
        <div class="pt-3 border-t border-white/[0.06]">
            <span class="text-xs text-white/30">Related: </span>
            <span class="text-xs text-white/60 font-medium">{{ $news->player->full_name }}</span>
        </div>
    @endif
</div>
