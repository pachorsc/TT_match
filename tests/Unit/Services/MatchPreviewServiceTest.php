<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\GameMatch;
use App\Models\News;
use App\Models\Player;
use App\Services\MatchPreviewService;
use App\Services\MatchService;
use App\Services\NewsService;
use App\Services\PlayerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchPreviewServiceTest extends TestCase
{
    use RefreshDatabase;

    private MatchPreviewService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new MatchPreviewService(
            new PlayerService,
            new MatchService,
            new NewsService,
        );
    }

    public function test_get_preview_data_returns_all_required_keys(): void
    {
        $match = GameMatch::factory()->completed()->create();
        News::factory()->count(3)->create();

        $result = $this->service->getPreviewData($match);

        $this->assertArrayHasKey('match', $result);
        $this->assertArrayHasKey('playerA', $result);
        $this->assertArrayHasKey('playerB', $result);
        $this->assertArrayHasKey('headToHead', $result);
        $this->assertArrayHasKey('tournament', $result);
        $this->assertArrayHasKey('news', $result);
    }

    public function test_get_preview_data_returns_player_stats(): void
    {
        $playerA = Player::factory()->create();
        $playerB = Player::factory()->create();

        $match = GameMatch::factory()->completed()->create([
            'player_a_id' => $playerA->id,
            'player_b_id' => $playerB->id,
        ]);

        $result = $this->service->getPreviewData($match);

        $this->assertArrayHasKey('stats', $result['playerA']);
        $this->assertArrayHasKey('last7', $result['playerA']);
        $this->assertArrayHasKey('stats', $result['playerB']);
        $this->assertArrayHasKey('last7', $result['playerB']);
    }

    public function test_get_preview_data_includes_head_to_head(): void
    {
        $playerA = Player::factory()->create();
        $playerB = Player::factory()->create();

        GameMatch::factory()->completed()
            ->betweenPlayers($playerA, $playerB)
            ->create(['winner_id' => $playerA->id]);

        $match = GameMatch::factory()->completed()->create([
            'player_a_id' => $playerA->id,
            'player_b_id' => $playerB->id,
            'winner_id' => $playerB->id,
        ]);

        $result = $this->service->getPreviewData($match);

        $this->assertEquals(2, $result['headToHead']['total_matches']);
        $this->assertEquals(1, $result['headToHead']['player_a_wins']);
        $this->assertEquals(1, $result['headToHead']['player_b_wins']);
    }

    public function test_get_preview_data_includes_tournament(): void
    {
        $match = GameMatch::factory()->completed()->create();

        $result = $this->service->getPreviewData($match);

        $this->assertEquals($match->tournament_id, $result['tournament']->id);
    }

    public function test_get_preview_data_includes_latest_news(): void
    {
        News::factory()->count(5)->create();
        $match = GameMatch::factory()->completed()->create();

        $result = $this->service->getPreviewData($match);

        $this->assertCount(5, $result['news']);
    }
}
