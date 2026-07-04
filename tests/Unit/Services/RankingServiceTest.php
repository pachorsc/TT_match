<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Player;
use App\Models\Ranking;
use App\Services\RankingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingServiceTest extends TestCase
{
    use RefreshDatabase;

    private RankingService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new RankingService;
    }

    public function test_get_current_ranking_returns_most_recent(): void
    {
        $player = Player::factory()->create();

        $oldRanking = Ranking::factory()->create([
            'player_id' => $player->id,
            'ranking_date' => now()->subWeeks(2),
        ]);

        $currentRanking = Ranking::factory()->create([
            'player_id' => $player->id,
            'ranking_date' => now()->subWeek(),
        ]);

        $result = $this->service->getCurrentRanking($player);

        $this->assertEquals($currentRanking->id, $result->id);
    }

    public function test_get_current_ranking_returns_null_when_no_rankings(): void
    {
        $player = Player::factory()->create();

        $result = $this->service->getCurrentRanking($player);

        $this->assertNull($result);
    }

    public function test_get_ranking_history_returns_ordered_by_date(): void
    {
        $player = Player::factory()->create();

        Ranking::factory()->create([
            'player_id' => $player->id,
            'ranking_date' => now()->subWeeks(3),
        ]);

        Ranking::factory()->create([
            'player_id' => $player->id,
            'ranking_date' => now()->subWeeks(2),
        ]);

        Ranking::factory()->create([
            'player_id' => $player->id,
            'ranking_date' => now()->subWeek(),
        ]);

        $result = $this->service->getRankingHistory($player);

        $this->assertCount(3, $result);
        $this->assertTrue($result->first()->ranking_date->gt($result->last()->ranking_date));
    }

    public function test_get_ranking_history_respects_limit(): void
    {
        $player = Player::factory()->create();

        Ranking::factory()->count(5)->create([
            'player_id' => $player->id,
        ]);

        $result = $this->service->getRankingHistory($player, 3);

        $this->assertCount(3, $result);
    }
}
