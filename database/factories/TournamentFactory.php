<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Tournament;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tournament>
 */
class TournamentFactory extends Factory
{
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-1 year', '+3 months');
        $endDate = (clone $startDate)->modify('+'.fake()->numberBetween(3, 14).' days');

        return [
            'name' => fake()->words(3, true).' Open',
            'location' => fake()->city(),
            'country' => fake()->country(),
            'country_code' => fake()->countryCode(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'category' => fake()->randomElement(['WTT Star Contender', 'WTT Contender', 'WTT Champions', 'ITTF World Tour', 'Continental']),
        ];
    }

    public function current(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => now()->subDays(2),
            'end_date' => now()->addDays(5),
        ]);
    }
}
