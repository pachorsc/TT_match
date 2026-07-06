<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Player;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

final class YouTubeService
{
    private const CACHE_TTL = 86400;

    private const MAX_RESULTS = 5;

    public function getPlayerVideos(Player $player): Collection
    {
        $cacheKey = "youtube.videos.{$player->id}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($player) {
            return $this->fetchFromYouTube($player);
        });
    }

    private function fetchFromYouTube(Player $player): Collection
    {
        $apiKey = config('services.youtube.api_key');
        $channelId = config('services.youtube.channel_id');

        if (blank($apiKey)) {
            return collect();
        }

        $response = Http::get('https://www.googleapis.com/youtube/v3/search', [
            'part' => 'snippet',
            'channelId' => $channelId,
            'q' => $player->full_name,
            'order' => 'date',
            'type' => 'video',
            'maxResults' => self::MAX_RESULTS,
            'publishedAfter' => now()->subYear()->toRfc3339String(),
            'key' => $apiKey,
        ]);

        if ($response->failed()) {
            return collect();
        }

        $items = $response->json('items', []);

        return collect($items)->map(fn (array $item) => (object) [
            'title' => $item['snippet']['title'] ?? '',
            'description' => $item['snippet']['description'] ?? '',
            'url' => 'https://www.youtube.com/watch?v='.($item['id']['videoId'] ?? ''),
            'youtube_video_id' => $item['id']['videoId'] ?? '',
            'thumbnail_url' => $item['snippet']['thumbnails']['high']['url']
                ?? $item['snippet']['thumbnails']['medium']['url']
                ?? $item['snippet']['thumbnails']['default']['url']
                ?? '',
            'published_at' => $item['snippet']['publishedAt'] ?? '',
        ]);
    }
}
