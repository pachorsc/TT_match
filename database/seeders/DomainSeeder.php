<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\GameMatch;
use App\Models\MatchSet;
use App\Models\News;
use App\Models\Player;
use App\Models\Ranking;
use App\Models\Tournament;
use Illuminate\Database\Seeder;

class DomainSeeder extends Seeder
{
    public function run(): void
    {
        $players = Player::factory()->count(20)->create();

        $tournaments = Tournament::factory()->count(5)->create();

        foreach ($tournaments as $tournament) {
            $matchCount = random_int(4, 8);

            for ($i = 0; $i < $matchCount; $i++) {
                $playerA = $players->random();
                $playerB = $players->where('id', '!=', $playerA->id)->random();

                $setsToWin = 3;
                $playerASets = random_int(0, $setsToWin);
                $playerBSets = random_int(0, $setsToWin);

                if ($playerASets === $playerBSets) {
                    $playerASets = $setsToWin;
                }

                $winner = $playerASets > $playerBSets ? $playerA : $playerB;

                $match = GameMatch::create([
                    'tournament_id' => $tournament->id,
                    'player_a_id' => $playerA->id,
                    'player_b_id' => $playerB->id,
                    'winner_id' => $winner->id,
                    'player_a_sets' => $playerASets,
                    'player_b_sets' => $playerBSets,
                    'match_date' => fake()->dateTimeBetween($tournament->start_date, $tournament->end_date),
                    'match_time' => fake()->time('H:i'),
                    'round' => fake()->randomElement(['Group Stage', 'Round of 16', 'Quarterfinal', 'Semifinal', 'Final']),
                    'status' => 'Completed',
                ]);

                $totalSets = $playerASets + $playerBSets;

                for ($setNum = 1; $setNum <= $totalSets; $setNum++) {
                    $playerAWon = $setNum <= $playerASets;

                    MatchSet::create([
                        'match_id' => $match->id,
                        'set_number' => $setNum,
                        'player_a_points' => $playerAWon ? random_int(11, 13) : random_int(5, 9),
                        'player_b_points' => $playerAWon ? random_int(5, 9) : random_int(11, 13),
                    ]);
                }
            }
        }

        foreach ($players as $index => $player) {
            Ranking::create([
                'player_id' => $player->id,
                'ranking' => $index + 1,
                'rating_points' => 4500 - ($index * 100),
                'ranking_date' => now()->subWeek(),
            ]);

            Ranking::create([
                'player_id' => $player->id,
                'ranking' => $index + 2,
                'rating_points' => 4400 - ($index * 100),
                'ranking_date' => now()->subWeeks(2),
            ]);
        }

        foreach ($players->take(10) as $player) {
            News::factory()
                ->count(random_int(1, 3))
                ->withPlayer($player)
                ->create();
        }
    }
}
