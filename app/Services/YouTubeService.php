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

    private const CACHE_VERSION = 2;

    private const MAX_RESULTS = 5;

    public function getPlayerVideos(Player $player): Collection
    {
        $cacheKey = 'youtube.videos.v'.self::CACHE_VERSION.'.player.'.$player->id;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($player) {
            return $this->fetchFromYouTube($player);
        });
    }

    private function fetchFromYouTube(Player $player): Collection
    {
        $apiKey = config('services.youtube.api_key');

        if (blank($apiKey)) {
            return collect();
        }

        $queries = array_unique([
            $player->full_name.' table tennis',
            $player->last_name.' '.$player->first_name.' table tennis',
        ]);

        $allVideos = collect();
        $seenVideoIds = [];

        foreach ($queries as $query) {
            if ($allVideos->count() >= self::MAX_RESULTS) {
                break;
            }

            $response = Http::get('https://www.googleapis.com/youtube/v3/search', [
                'part' => 'snippet',
                'q' => $query,
                'order' => 'relevance',
                'type' => 'video',
                'maxResults' => self::MAX_RESULTS + 5,
                'publishedAfter' => now()->subYear()->toRfc3339String(),
                'key' => $apiKey,
            ]);

            if ($response->failed()) {
                continue;
            }

            $items = $response->json('items', []);

            foreach ($items as $item) {
                $videoId = $item['id']['videoId'] ?? '';

                if (! $videoId || isset($seenVideoIds[$videoId])) {
                    continue;
                }

                $seenVideoIds[$videoId] = true;

                $allVideos->push((object) [
                    'title' => $item['snippet']['title'] ?? '',
                    'description' => $item['snippet']['description'] ?? '',
                    'url' => 'https://www.youtube.com/watch?v='.$videoId,
                    'youtube_video_id' => $videoId,
                    'thumbnail_url' => $item['snippet']['thumbnails']['high']['url']
                        ?? $item['snippet']['thumbnails']['medium']['url']
                        ?? $item['snippet']['thumbnails']['default']['url']
                        ?? '',
                    'published_at' => $item['snippet']['publishedAt'] ?? '',
                    'channel_title' => $item['snippet']['channelTitle'] ?? '',
                ]);
            }
        }

        return $allVideos->take(self::MAX_RESULTS);
    }
}
