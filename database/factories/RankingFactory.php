<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Player;
use App\Models\Ranking;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ranking>
 */
class RankingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'player_id' => Player::factory(),
            'ranking' => fake()->numberBetween(1, 500),
            'rating_points' => fake()->numberBetween(500, 5000),
            'ranking_date' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function topRanked(): static
    {
        return $this->state(fn (array $attributes) => [
            'ranking' => fake()->numberBetween(1, 20),
            'rating_points' => fake()->numberBetween(3000, 5000),
        ]);
    }
}
