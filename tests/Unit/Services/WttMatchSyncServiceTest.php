<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\GameMatch;
use App\Models\Player;
use App\Models\Tournament;
use App\Services\WttApiClient;
use App\Services\WttMatchSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

final class WttMatchSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    private WttApiClient&MockInterface $apiClient;

    private WttMatchSyncService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiClient = \Mockery::mock(WttApiClient::class);
        $this->service = new WttMatchSyncService($this->apiClient);
    }

    public function test_creates_new_matches(): void
    {
        $playerA = Player::factory()->create(['wtt_id' => '1001']);
        $playerB = Player::factory()->create(['wtt_id' => '1002']);

        $this->mockApiResponse([
            [
                'document_code' => 'MATCH-001',
                'sub_event' => "Men's Singles",
                'player_a_ittf_id' => '1001',
                'player_b_ittf_id' => '1002',
                'overall_scores' => '3-1',
                'game_scores' => '11:7,9:11,11:5,11:8',
                'winner_ittf_id' => '1001',
                'date' => '2026-07-01T10:00:00',
                'completed' => true,
                'player_a_sets' => 3,
                'player_b_sets' => 1,
            ],
        ]);

        $result = $this->service->sync(3242);

        $this->assertSame(1, $result['created']);
        $this->assertSame(0, $result['updated']);
        $this->assertSame(0, $result['skipped']);

        $this->assertDatabaseHas('matches', [
            'ittf_id' => 'MATCH-001',
            'player_a_id' => $playerA->id,
            'player_b_id' => $playerB->id,
            'winner_id' => $playerA->id,
            'player_a_sets' => 3,
            'player_b_sets' => 1,
            'status' => 'Completed',
        ]);

        $this->assertDatabaseHas('match_sets', ['match_id' => 1, 'set_number' => 1, 'player_a_points' => 11, 'player_b_points' => 7]);
        $this->assertDatabaseHas('match_sets', ['match_id' => 1, 'set_number' => 2, 'player_a_points' => 9, 'player_b_points' => 11]);
        $this->assertDatabaseHas('match_sets', ['match_id' => 1, 'set_number' => 3, 'player_a_points' => 11, 'player_b_points' => 5]);
        $this->assertDatabaseHas('match_sets', ['match_id' => 1, 'set_number' => 4, 'player_a_points' => 11, 'player_b_points' => 8]);
    }

    public function test_skips_existing_unchanged_matches(): void
    {
        $tournament = Tournament::factory()->create(['name' => 'Test Event 2026']);
        $playerA = Player::factory()->create(['wtt_id' => '1001']);
        $playerB = Player::factory()->create(['wtt_id' => '1002']);

        GameMatch::factory()->create([
            'tournament_id' => $tournament->id,
            'ittf_id' => 'MATCH-001',
            'player_a_id' => $playerA->id,
            'player_b_id' => $playerB->id,
            'winner_id' => $playerA->id,
            'player_a_sets' => 3,
            'player_b_sets' => 1,
            'status' => 'Completed',
        ]);

        $this->mockApiResponse([
            [
                'document_code' => 'MATCH-001',
                'sub_event' => "Men's Singles",
                'player_a_ittf_id' => '1001',
                'player_b_ittf_id' => '1002',
                'overall_scores' => '3-1',
                'game_scores' => '',
                'winner_ittf_id' => '1001',
                'date' => now()->format('Y-m-d\TH:i:s'),
                'completed' => true,
                'player_a_sets' => 3,
                'player_b_sets' => 1,
            ],
        ]);

        $result = $this->service->sync(3242);

        $this->assertSame(0, $result['created']);
        $this->assertSame(0, $result['updated']);
        $this->assertSame(1, $result['skipped']);
    }

    public function test_updates_existing_changed_match(): void
    {
        $tournament = Tournament::factory()->create(['name' => 'Test Event 2026']);
        $playerA = Player::factory()->create(['wtt_id' => '1001']);
        $playerB = Player::factory()->create(['wtt_id' => '1002']);

        $match = GameMatch::factory()->create([
            'tournament_id' => $tournament->id,
            'ittf_id' => 'MATCH-001',
            'player_a_id' => $playerA->id,
            'player_b_id' => $playerB->id,
            'winner_id' => null,
            'player_a_sets' => 0,
            'player_b_sets' => 0,
            'status' => 'Scheduled',
        ]);

        $this->mockApiResponse([
            [
                'document_code' => 'MATCH-001',
                'sub_event' => "Men's Singles",
                'player_a_ittf_id' => '1001',
                'player_b_ittf_id' => '1002',
                'overall_scores' => '3-1',
                'game_scores' => '11:7,9:11,11:5,11:8',
                'winner_ittf_id' => '1001',
                'date' => now()->format('Y-m-d\TH:i:s'),
                'completed' => true,
                'player_a_sets' => 3,
                'player_b_sets' => 1,
            ],
        ]);

        $result = $this->service->sync(3242);

        $this->assertSame(0, $result['created']);
        $this->assertSame(1, $result['updated']);
        $this->assertSame(0, $result['skipped']);

        $this->assertDatabaseHas('matches', [
            'id' => $match->id,
            'status' => 'Completed',
            'winner_id' => $playerA->id,
            'player_a_sets' => 3,
            'player_b_sets' => 1,
        ]);
    }

    public function test_skips_match_with_unknown_players(): void
    {
        $playerA = Player::factory()->create(['wtt_id' => '1001']);

        $this->mockApiResponse([
            [
                'document_code' => 'MATCH-001',
                'sub_event' => "Men's Singles",
                'player_a_ittf_id' => '1001',
                'player_b_ittf_id' => '99999', // not in DB
                'overall_scores' => '',
                'game_scores' => '',
                'winner_ittf_id' => '',
                'date' => '',
                'completed' => false,
                'player_a_sets' => 0,
                'player_b_sets' => 0,
            ],
        ]);

        $result = $this->service->sync(3242);

        $this->assertSame(0, $result['created']);
        $this->assertSame(0, $result['updated']);
        $this->assertSame(1, $result['skipped']);
    }

    public function test_skips_match_with_missing_player_ids(): void
    {
        $this->mockApiResponse([
            [
                'document_code' => 'MATCH-001',
                'sub_event' => "Men's Singles",
                'player_a_ittf_id' => '',
                'player_b_ittf_id' => '',
                'overall_scores' => '',
                'game_scores' => '',
                'winner_ittf_id' => '',
                'date' => '',
                'completed' => false,
                'player_a_sets' => 0,
                'player_b_sets' => 0,
            ],
        ]);

        $result = $this->service->sync(3242);

        $this->assertSame(0, $result['created']);
        $this->assertSame(0, $result['updated']);
        $this->assertSame(1, $result['skipped']);
    }

    public function test_skips_match_with_same_player_twice(): void
    {
        $playerA = Player::factory()->create(['wtt_id' => '1001']);

        $this->mockApiResponse([
            [
                'document_code' => 'MATCH-001',
                'sub_event' => "Men's Singles",
                'player_a_ittf_id' => '1001',
                'player_b_ittf_id' => '1001',
                'overall_scores' => '',
                'game_scores' => '',
                'winner_ittf_id' => '',
                'date' => '',
                'completed' => false,
                'player_a_sets' => 0,
                'player_b_sets' => 0,
            ],
        ]);

        $result = $this->service->sync(3242);

        $this->assertSame(0, $result['created']);
        $this->assertSame(0, $result['updated']);
        $this->assertSame(1, $result['skipped']);
    }

    public function test_processes_multiple_matches_mixed_scenarios(): void
    {
        $tournament = Tournament::factory()->create(['name' => 'Test Event 2026']);
        $playerA = Player::factory()->create(['wtt_id' => '1001']);
        $playerB = Player::factory()->create(['wtt_id' => '1002']);
        $playerC = Player::factory()->create(['wtt_id' => '1003']);

        // Existing match that won't change
        GameMatch::factory()->create([
            'tournament_id' => $tournament->id,
            'ittf_id' => 'MATCH-001',
            'player_a_id' => $playerA->id,
            'player_b_id' => $playerC->id,
            'winner_id' => $playerA->id,
            'player_a_sets' => 3,
            'player_b_sets' => 0,
            'status' => 'Completed',
        ]);

        $this->mockApiResponse([
            [ // new match
                'document_code' => 'MATCH-002',
                'sub_event' => "Women's Singles",
                'player_a_ittf_id' => '1002',
                'player_b_ittf_id' => '1003',
                'overall_scores' => '3-2',
                'game_scores' => '11:9,7:11,11:8,6:11,11:6',
                'winner_ittf_id' => '1002',
                'date' => '2026-07-02T14:00:00',
                'completed' => true,
                'player_a_sets' => 3,
                'player_b_sets' => 2,
            ],
            [ // existing unchanged — should skip
                'document_code' => 'MATCH-001',
                'sub_event' => "Men's Singles",
                'player_a_ittf_id' => '1001',
                'player_b_ittf_id' => '1003',
                'overall_scores' => '3-0',
                'game_scores' => '',
                'winner_ittf_id' => '1001',
                'date' => now()->format('Y-m-d\TH:i:s'),
                'completed' => true,
                'player_a_sets' => 3,
                'player_b_sets' => 0,
            ],
        ]);

        $result = $this->service->sync(3242);

        $this->assertSame(1, $result['created']);
        $this->assertSame(0, $result['updated']);
        $this->assertSame(1, $result['skipped']);

        $this->assertDatabaseHas('matches', ['ittf_id' => 'MATCH-002']);
    }

    public function test_creates_tournament_when_not_exists(): void
    {
        $playerA = Player::factory()->create(['wtt_id' => '1001']);
        $playerB = Player::factory()->create(['wtt_id' => '1002']);

        $this->apiClient
            ->shouldReceive('fetchAllMatches')
            ->with(3242)
            ->andReturn([
                'matches' => [
                    [
                        'document_code' => 'MATCH-001',
                        'sub_event' => "Men's Singles",
                        'player_a_ittf_id' => '1001',
                        'player_b_ittf_id' => '1002',
                        'overall_scores' => '3-0',
                        'game_scores' => '',
                        'winner_ittf_id' => '1001',
                        'date' => '2026-07-01T10:00:00',
                        'completed' => true,
                        'player_a_sets' => 3,
                        'player_b_sets' => 0,
                    ],
                ],
                'competition' => ['Name' => 'New Test Event 2026', 'event_id' => 3242],
            ]);

        $result = $this->service->sync(3242);

        $this->assertSame(1, $result['created']);
        $this->assertDatabaseHas('tournaments', ['name' => 'New Test Event 2026']);
    }

    private function mockApiResponse(array $matches): void
    {
        $this->apiClient
            ->shouldReceive('fetchAllMatches')
            ->with(3242)
            ->andReturn([
                'matches' => $matches,
                'competition' => ['Name' => 'Test Event 2026'],
            ]);
    }
}
