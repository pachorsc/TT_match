<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\GameMatch;
use App\Models\MatchSet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MatchSet>
 */
class MatchSetFactory extends Factory
{
    public function definition(): array
    {
        $winnerPoints = fake()->numberBetween(11, 13);
        $loserPoints = fake()->numberBetween(0, $winnerPoints - 2);

        return [
            'match_id' => GameMatch::factory(),
            'set_number' => fake()->numberBetween(1, 7),
            'player_a_points' => $winnerPoints,
            'player_b_points' => $loserPoints,
        ];
    }

    public function playerBWon(): static
    {
        return $this->state(fn (array $attributes) => [
            'player_a_points' => fake()->numberBetween(0, 9),
            'player_b_points' => fake()->numberBetween(11, 13),
        ]);
    }

    public function close(): static
    {
        return $this->state(fn (array $attributes) => [
            'player_a_points' => fake()->numberBetween(10, 13),
            'player_b_points' => fake()->numberBetween(10, 13),
        ]);
    }
}
