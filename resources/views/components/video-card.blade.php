@props(['video', 'playerName' => null])

<a href="{{ $video->url }}"
   target="_blank"
   rel="noopener noreferrer"
   class="card-glass group block overflow-hidden cursor-pointer">
    <div class="relative aspect-video overflow-hidden rounded-t-2xl bg-black/40">
        <img src="{{ $video->thumbnail_url }}"
             alt="{{ $video->title }}"
             class="w-full h-full object-cover transition-all duration-500 group-hover:scale-105"
             loading="lazy">
        <div class="absolute inset-0 flex items-center justify-center bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm">
                <svg class="w-5 h-5 text-white ml-0.5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M8 5v14l11-7z"/>
                </svg>
            </div>
        </div>
    </div>
    <div class="p-4 space-y-2">
        <h4 class="text-sm font-bold leading-snug line-clamp-2 text-white/90 group-hover:text-sport-400 transition-colors duration-200">
            {{ $video->title }}
        </h4>
        @if($video->description)
            <p class="text-xs text-white/40 leading-relaxed line-clamp-2">{{ $video->description }}</p>
        @endif
        <div class="flex items-center justify-between gap-3 pt-2">
            <span class="text-xs text-white/30">{{ \Carbon\Carbon::parse($video->published_at)->diffForHumans() }}</span>
            @if($playerName)
                <span class="text-xs text-white/50 font-medium truncate">{{ $playerName }}</span>
            @endif
        </div>
    </div>
</a>
