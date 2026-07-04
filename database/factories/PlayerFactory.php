<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Player;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Player>
 */
class PlayerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'country' => fake()->country(),
            'country_code' => fake()->countryCode(),
            'date_of_birth' => fake()->dateTimeBetween('-40 years', '-18 years'),
            'height_cm' => fake()->numberBetween(155, 210),
            'dominant_hand' => fake()->randomElement(['Left', 'Right']),
            'playing_style' => fake()->randomElement(['Offensive', 'Defensive', 'All-round']),
            'world_ranking' => fake()->numberBetween(1, 500),
            'rating_points' => fake()->numberBetween(500, 5000),
        ];
    }

    public function topRanked(): static
    {
        return $this->state(fn (array $attributes) => [
            'world_ranking' => fake()->numberBetween(1, 20),
            'rating_points' => fake()->numberBetween(3000, 5000),
        ]);
    }

    public function rightHanded(): static
    {
        return $this->state(fn (array $attributes) => [
            'dominant_hand' => 'Right',
        ]);
    }

    public function leftHanded(): static
    {
        return $this->state(fn (array $attributes) => [
            'dominant_hand' => 'Left',
        ]);
    }
}
