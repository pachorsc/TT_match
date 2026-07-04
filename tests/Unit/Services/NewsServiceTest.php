<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\News;
use App\Models\Player;
use App\Services\NewsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsServiceTest extends TestCase
{
    use RefreshDatabase;

    private NewsService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new NewsService;
    }

    public function test_get_latest_news_returns_most_recent(): void
    {
        $old = News::factory()->create([
            'headline' => 'Old article',
            'published_at' => now()->subDays(5),
        ]);
        $mid = News::factory()->create([
            'headline' => 'Mid article',
            'published_at' => now()->subDays(3),
        ]);
        $new = News::factory()->create([
            'headline' => 'New article',
            'published_at' => now()->subDay(),
        ]);

        $result = $this->service->getLatestNews();

        $this->assertCount(3, $result);
        $this->assertEquals($new->id, $result[0]->id);
        $this->assertEquals($mid->id, $result[1]->id);
        $this->assertEquals($old->id, $result[2]->id);
    }

    public function test_get_latest_news_respects_limit(): void
    {
        News::factory()->count(10)->create();

        $result = $this->service->getLatestNews(3);

        $this->assertCount(3, $result);
    }

    public function test_get_latest_news_loads_player_relation(): void
    {
        $player = Player::factory()->create();
        News::factory()->withPlayer($player)->create();

        $result = $this->service->getLatestNews();

        $this->assertNotNull($result->first()->player);
        $this->assertEquals($player->id, $result->first()->player->id);
    }

    public function test_get_news_by_player_returns_only_that_player_news(): void
    {
        $player = Player::factory()->create();
        $otherPlayer = Player::factory()->create();

        News::factory()->withPlayer($player)->count(3)->create();
        News::factory()->withPlayer($otherPlayer)->count(2)->create();

        $result = $this->service->getNewsByPlayer($player);

        $this->assertCount(3, $result);
        $result->each(fn ($news) => $this->assertEquals($player->id, $news->player_id));
    }

    public function test_get_news_by_player_respects_limit(): void
    {
        $player = Player::factory()->create();
        News::factory()->withPlayer($player)->count(10)->create();

        $result = $this->service->getNewsByPlayer($player, 2);

        $this->assertCount(2, $result);
    }
}
