<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\GameMatch;
use App\Models\Player;
use App\Models\Ranking;
use App\Models\Tournament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlayerProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_returns_200_for_valid_player(): void
    {
        $player = Player::factory()->create();

        $response = $this->get("/players/{$player->id}");

        $response->assertStatus(200);
        $response->assertSee($player->full_name);
        $response->assertSee($player->country_code);
    }

    public function test_profile_page_returns_404_for_non_existent_player(): void
    {
        $response = $this->get('/players/99999');

        $response->assertStatus(404);
    }

    public function test_profile_page_displays_career_stats(): void
    {
        $player = Player::factory()->create();

        GameMatch::factory()->completed()->count(5)->create([
            'player_a_id' => $player->id,
            'winner_id' => $player->id,
        ]);

        GameMatch::factory()->completed()->count(3)->create([
            'player_b_id' => $player->id,
            'winner_id' => $player->id,
        ]);

        $opponent = Player::factory()->create();
        GameMatch::factory()->completed()->create([
            'player_a_id' => $player->id,
            'player_b_id' => $opponent->id,
            'winner_id' => $opponent->id,
        ]);

        $response = $this->get("/players/{$player->id}");

        $response->assertStatus(200);
        $response->assertSee('9'); // total matches (5 + 3 + 1)
        $response->assertSee('8'); // wins
        $response->assertSee('1'); // loss
    }

    public function test_profile_page_displays_ranking_history(): void
    {
        $player = Player::factory()->create();

        Ranking::factory()->create([
            'player_id' => $player->id,
            'ranking' => 10,
            'rating_points' => 4000,
            'ranking_date' => '2026-07-01',
        ]);

        Ranking::factory()->create([
            'player_id' => $player->id,
            'ranking' => 15,
            'rating_points' => 3800,
            'ranking_date' => '2026-06-01',
        ]);

        $response = $this->get("/players/{$player->id}");

        $response->assertStatus(200);
        $response->assertSee('#10');
        $response->assertSee('4,000');
    }

    public function test_profile_page_displays_current_streak(): void
    {
        $player = Player::factory()->create();
        $opponent = Player::factory()->create();

        GameMatch::factory()->completed()->create([
            'player_a_id' => $player->id,
            'player_b_id' => $opponent->id,
            'winner_id' => $player->id,
            'match_date' => '2026-07-05',
        ]);

        GameMatch::factory()->completed()->create([
            'player_a_id' => $player->id,
            'player_b_id' => $opponent->id,
            'winner_id' => $player->id,
            'match_date' => '2026-07-04',
        ]);

        $response = $this->get("/players/{$player->id}");

        $response->assertStatus(200);
        $response->assertSee('Current Streak');
        $response->assertSee('W');
    }

    public function test_profile_page_shows_match_history(): void
    {
        $player = Player::factory()->create();

        GameMatch::factory()
            ->count(25)
            ->completed()
            ->create(['player_a_id' => $player->id]);

        $response = $this->get("/players/{$player->id}");

        $response->assertStatus(200);
        $response->assertSee('Match History');
    }

    public function test_profile_page_can_filter_by_year(): void
    {
        $player = Player::factory()->create();
        $tournament = Tournament::factory()->create();

        GameMatch::factory()->completed()->create([
            'player_a_id' => $player->id,
            'match_date' => '2026-05-01',
            'tournament_id' => $tournament->id,
        ]);

        GameMatch::factory()->completed()->create([
            'player_a_id' => $player->id,
            'match_date' => '2025-05-01',
            'tournament_id' => $tournament->id,
        ]);

        $response = $this->get("/players/{$player->id}?year=2025");

        $response->assertStatus(200);
    }

    public function test_profile_page_can_filter_by_tournament(): void
    {
        $player = Player::factory()->create();
        $tournamentA = Tournament::factory()->create(['name' => 'WTT Champions']);
        $tournamentB = Tournament::factory()->create(['name' => 'WTT Star Contender']);

        GameMatch::factory()->completed()->create([
            'player_a_id' => $player->id,
            'tournament_id' => $tournamentA->id,
        ]);

        GameMatch::factory()->completed()->create([
            'player_a_id' => $player->id,
            'tournament_id' => $tournamentB->id,
        ]);

        $response = $this->get("/players/{$player->id}?tournament_id={$tournamentA->id}");

        $response->assertStatus(200);
    }
}
