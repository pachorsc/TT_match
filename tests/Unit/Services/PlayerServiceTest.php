<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\GameMatch;
use App\Models\Player;
use App\Models\Ranking;
use App\Services\PlayerService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlayerServiceTest extends TestCase
{
    use RefreshDatabase;

    private PlayerService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new PlayerService;
    }

    public function test_get_player_by_id_returns_player_with_rankings(): void
    {
        $player = Player::factory()->create();
        Ranking::factory()->create(['player_id' => $player->id]);

        $result = $this->service->getPlayerById($player->id);

        $this->assertEquals($player->id, $result->id);
        $this->assertNotEmpty($result->rankings);
    }

    public function test_get_player_by_id_throws_exception_when_not_found(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->getPlayerById(999);
    }

    public function test_get_player_stats_returns_correct_counts(): void
    {
        $player = Player::factory()->create();

        $match1 = GameMatch::factory()->completed()->create([
            'player_a_id' => $player->id,
            'winner_id' => $player->id,
        ]);

        $match2 = GameMatch::factory()->completed()->create([
            'player_b_id' => $player->id,
            'winner_id' => $player->id,
        ]);

        $opponent = Player::factory()->create();
        $match3 = GameMatch::factory()->completed()->create([
            'player_a_id' => $player->id,
            'player_b_id' => $opponent->id,
            'winner_id' => $opponent->id,
        ]);

        $stats = $this->service->getPlayerStats($player);

        $this->assertEquals(3, $stats['total_matches']);
        $this->assertEquals(2, $stats['wins']);
        $this->assertEquals(1, $stats['losses']);
        $this->assertEquals(66.7, $stats['win_rate']);
    }

    public function test_get_player_stats_returns_zeros_when_no_matches(): void
    {
        $player = Player::factory()->create();

        $stats = $this->service->getPlayerStats($player);

        $this->assertEquals(0, $stats['total_matches']);
        $this->assertEquals(0, $stats['wins']);
        $this->assertEquals(0, $stats['losses']);
        $this->assertEquals(0.0, $stats['win_rate']);
    }

    public function test_get_last_7_matches_returns_most_recent_completed(): void
    {
        $player = Player::factory()->create();

        $matches = GameMatch::factory()
            ->count(10)
            ->completed()
            ->create([
                'player_a_id' => $player->id,
            ]);

        $result = $this->service->getLast7Matches($player);

        $this->assertCount(7, $result);
        $this->assertEquals(
            $matches->sortBy([
                ['match_date', 'desc'],
                ['id', 'desc'],
            ])->take(7)->pluck('id')->values(),
            $result->pluck('id')->values()
        );
    }

    public function test_get_last_7_matches_excludes_scheduled(): void
    {
        $player = Player::factory()->create();

        GameMatch::factory()->completed()
            ->count(3)
            ->create(['player_a_id' => $player->id]);

        GameMatch::factory()->scheduled()
            ->count(3)
            ->create(['player_a_id' => $player->id]);

        $result = $this->service->getLast7Matches($player);

        $this->assertCount(3, $result);
    }
}
