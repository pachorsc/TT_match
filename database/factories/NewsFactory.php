<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\News;
use App\Models\Player;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<News>
 */
class NewsFactory extends Factory
{
    public function definition(): array
    {
        return [
            'player_id' => Player::factory(),
            'headline' => fake()->sentence(),
            'summary' => fake()->paragraph(),
            'source' => fake()->randomElement(['ITTF', 'World Table Tennis', 'Table Tennis Daily', 'PingPong365', 'Tennis Actu']),
            'url' => fake()->url(),
            'published_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function withPlayer(Player $player): static
    {
        return $this->state(fn (array $attributes) => [
            'player_id' => $player->id,
        ]);
    }
}
