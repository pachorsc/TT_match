<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\GameMatch;
use App\Models\Player;
use App\Models\Tournament;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GameMatch>
 */
class GameMatchFactory extends Factory
{
    public function definition(): array
    {
        $playerA = Player::factory();
        $playerB = Player::factory();
        $totalSets = fake()->randomElement([3, 5, 7]);
        $setsToWin = (int) ceil($totalSets / 2);
        $playerASets = fake()->numberBetween(0, $setsToWin);
        $playerBSets = fake()->numberBetween(0, $setsToWin);

        if ($playerASets === $playerBSets) {
            $playerASets = $setsToWin;
        }

        $winnerId = $playerASets > $playerBSets ? $playerA : $playerB;

        return [
            'tournament_id' => Tournament::factory(),
            'player_a_id' => $playerA,
            'player_b_id' => $playerB,
            'winner_id' => $winnerId,
            'player_a_sets' => $playerASets,
            'player_b_sets' => $playerBSets,
            'match_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'match_time' => fake()->time('H:i'),
            'round' => fake()->randomElement(['Group Stage', 'Round of 32', 'Round of 16', 'Quarterfinal', 'Semifinal', 'Final']),
            'status' => 'Completed',
        ];
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'winner_id' => null,
            'player_a_sets' => 0,
            'player_b_sets' => 0,
            'match_date' => fake()->dateTimeBetween('+1 day', '+1 month'),
            'status' => 'Scheduled',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Completed',
        ]);
    }

    public function betweenPlayers(Player $playerA, Player $playerB): static
    {
        return $this->state(fn (array $attributes) => [
            'player_a_id' => $playerA->id,
            'player_b_id' => $playerB->id,
        ]);
    }
}
