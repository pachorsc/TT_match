<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Player;
use App\Services\YouTubeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class YouTubeServiceTest extends TestCase
{
    use RefreshDatabase;

    private YouTubeService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        $this->service = new YouTubeService;
    }

    public function test_returns_empty_collection_when_api_key_missing(): void
    {
        config(['services.youtube.api_key' => null]);

        $player = Player::factory()->create();

        $result = $this->service->getPlayerVideos($player);

        $this->assertCount(0, $result);
    }

    public function test_returns_empty_collection_on_api_failure(): void
    {
        config(['services.youtube.api_key' => 'test-key']);

        Http::fake([
            'https://www.googleapis.com/youtube/v3/search*' => Http::response(null, 500),
        ]);

        $player = Player::factory()->create();

        $result = $this->service->getPlayerVideos($player);

        $this->assertCount(0, $result);
    }

    public function test_returns_videos_from_api_response(): void
    {
        config(['services.youtube.api_key' => 'test-key']);

        Http::fake([
            'https://www.googleapis.com/youtube/v3/search*' => Http::response([
                'items' => [
                    [
                        'id' => ['videoId' => 'abc123'],
                        'snippet' => [
                            'title' => 'Ma Long vs Fan Zhendong | WTT Finals',
                            'description' => 'Amazing match highlights',
                            'thumbnails' => [
                                'high' => ['url' => 'https://img.youtube.com/vi/abc123/hqdefault.jpg'],
                            ],
                            'publishedAt' => '2025-11-20T14:30:00Z',
                        ],
                    ],
                    [
                        'id' => ['videoId' => 'def456'],
                        'snippet' => [
                            'title' => 'Top 10 Points of the Year',
                            'description' => 'Incredible rallies',
                            'thumbnails' => [
                                'medium' => ['url' => 'https://img.youtube.com/vi/def456/mqdefault.jpg'],
                            ],
                            'publishedAt' => '2025-10-15T10:00:00Z',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $player = Player::factory()->create();

        $result = $this->service->getPlayerVideos($player);

        $this->assertCount(2, $result);

        $this->assertEquals('Ma Long vs Fan Zhendong | WTT Finals', $result[0]->title);
        $this->assertEquals('abc123', $result[0]->youtube_video_id);
        $this->assertEquals('https://www.youtube.com/watch?v=abc123', $result[0]->url);
        $this->assertEquals('https://img.youtube.com/vi/abc123/hqdefault.jpg', $result[0]->thumbnail_url);

        $this->assertEquals('Top 10 Points of the Year', $result[1]->title);
        $this->assertEquals('def456', $result[1]->youtube_video_id);
    }

    public function test_caches_results(): void
    {
        config(['services.youtube.api_key' => 'test-key']);

        Http::fake([
            'https://www.googleapis.com/youtube/v3/search*' => Http::response([
                'items' => [
                    [
                        'id' => ['videoId' => 'abc123'],
                        'snippet' => [
                            'title' => 'Test Video',
                            'description' => 'Test',
                            'thumbnails' => [
                                'default' => ['url' => 'https://img.youtube.com/vi/abc123/default.jpg'],
                            ],
                            'publishedAt' => now()->toRfc3339String(),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $player = Player::factory()->create();
        $cacheKey = "youtube.videos.{$player->id}";

        $this->assertFalse(Cache::has($cacheKey));

        $this->service->getPlayerVideos($player);

        $this->assertTrue(Cache::has($cacheKey));

        Http::assertSentCount(1);

        $this->service->getPlayerVideos($player);

        Http::assertSentCount(1);
    }
}
