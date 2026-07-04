<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\GameMatch;
use App\Models\Player;
use App\Services\MatchService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class MatchServiceTest extends TestCase
{
    use RefreshDatabase;

    private MatchService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new MatchService;
    }

    public function test_get_match_by_id_returns_match_with_relations(): void
    {
        $match = GameMatch::factory()->completed()->create();

        $result = $this->service->getMatchById($match->id);

        $this->assertEquals($match->id, $result->id);
        $this->assertNotNull($result->tournament);
        $this->assertNotNull($result->playerA);
        $this->assertNotNull($result->playerB);
        $this->assertNotNull($result->winner);
    }

    public function test_get_match_by_id_throws_exception_when_not_found(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->getMatchById(999);
    }

    public function test_get_head_to_head_returns_correct_counts(): void
    {
        $playerA = Player::factory()->create();
        $playerB = Player::factory()->create();

        GameMatch::factory()->completed()
            ->betweenPlayers($playerA, $playerB)
            ->create(['winner_id' => $playerA->id]);

        GameMatch::factory()->completed()
            ->betweenPlayers($playerA, $playerB)
            ->create(['winner_id' => $playerA->id]);

        GameMatch::factory()->completed()
            ->betweenPlayers($playerA, $playerB)
            ->create(['winner_id' => $playerB->id]);

        $result = $this->service->getHeadToHead($playerA->id, $playerB->id);

        $this->assertEquals(3, $result['total_matches']);
        $this->assertEquals(2, $result['player_a_wins']);
        $this->assertEquals(1, $result['player_b_wins']);
    }

    public function test_get_head_to_head_only_includes_last_2_years(): void
    {
        $playerA = Player::factory()->create();
        $playerB = Player::factory()->create();

        GameMatch::factory()->completed()
            ->betweenPlayers($playerA, $playerB)
            ->create([
                'winner_id' => $playerA->id,
                'match_date' => Carbon::now()->subYear(),
            ]);

        GameMatch::factory()->completed()
            ->betweenPlayers($playerA, $playerB)
            ->create([
                'winner_id' => $playerB->id,
                'match_date' => Carbon::now()->subYears(3),
            ]);

        $result = $this->service->getHeadToHead($playerA->id, $playerB->id);

        $this->assertEquals(1, $result['total_matches']);
        $this->assertEquals(1, $result['player_a_wins']);
        $this->assertEquals(0, $result['player_b_wins']);
    }

    public function test_get_head_to_head_excludes_matches_with_other_players(): void
    {
        $playerA = Player::factory()->create();
        $playerB = Player::factory()->create();
        $playerC = Player::factory()->create();

        GameMatch::factory()->completed()
            ->betweenPlayers($playerA, $playerB)
            ->create(['winner_id' => $playerA->id]);

        GameMatch::factory()->completed()
            ->create([
                'player_a_id' => $playerA->id,
                'player_b_id' => $playerC->id,
                'winner_id' => $playerA->id,
            ]);

        $result = $this->service->getHeadToHead($playerA->id, $playerB->id);

        $this->assertEquals(1, $result['total_matches']);
    }

    public function test_get_head_to_head_returns_empty_when_no_matches(): void
    {
        $playerA = Player::factory()->create();
        $playerB = Player::factory()->create();

        $result = $this->service->getHeadToHead($playerA->id, $playerB->id);

        $this->assertEquals(0, $result['total_matches']);
        $this->assertCount(0, $result['matches']);
    }

    public function test_get_recent_matches_between_returns_limited_results(): void
    {
        $playerA = Player::factory()->create();
        $playerB = Player::factory()->create();

        GameMatch::factory()->completed()
            ->count(5)
            ->betweenPlayers($playerA, $playerB)
            ->create();

        $result = $this->service->getRecentMatchesBetween($playerA, $playerB, 3);

        $this->assertCount(3, $result);
    }
}
