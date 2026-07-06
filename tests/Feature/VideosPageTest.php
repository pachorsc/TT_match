<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VideosPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_videos_page_returns_200(): void
    {
        $response = $this->get('/videos');

        $response->assertStatus(200);
        $response->assertSee('Videos');
        $response->assertSee('Buscar videos');
    }

    public function test_api_videos_endpoint_returns_json(): void
    {
        $player = Player::factory()->create();

        $response = $this->getJson("/api/players/{$player->id}/videos");

        $response->assertStatus(200);
        $response->assertJsonStructure(['player', 'videos']);
    }
}
