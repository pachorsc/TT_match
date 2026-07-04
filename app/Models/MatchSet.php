<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class MatchSet extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'match_id',
        'set_number',
        'player_a_points',
        'player_b_points',
    ];

    protected $casts = [
        'set_number' => 'integer',
        'player_a_points' => 'integer',
        'player_b_points' => 'integer',
    ];

    public function match(): BelongsTo
    {
        return $this->belongsTo(GameMatch::class);
    }

    public function getWinnerAttribute(): ?int
    {
        if ($this->player_a_points > $this->player_b_points) {
            return 1;
        }

        if ($this->player_b_points > $this->player_a_points) {
            return 2;
        }

        return null;
    }
}
