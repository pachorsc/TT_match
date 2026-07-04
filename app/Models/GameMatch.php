<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class GameMatch extends Model
{
    use HasFactory;

    protected $table = 'matches';

    protected $fillable = [
        'tournament_id',
        'player_a_id',
        'player_b_id',
        'winner_id',
        'player_a_sets',
        'player_b_sets',
        'match_date',
        'match_time',
        'round',
        'status',
    ];

    protected $casts = [
        'match_date' => 'date',
        'match_time' => 'datetime:H:i',
        'player_a_sets' => 'integer',
        'player_b_sets' => 'integer',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function playerA(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_a_id');
    }

    public function playerB(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_b_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'winner_id');
    }

    public function sets(): HasMany
    {
        return $this->hasMany(MatchSet::class, 'match_id');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'Completed');
    }

    public function scopeBetweenPlayers($query, int $playerAId, int $playerBId)
    {
        return $query->where(function ($q) use ($playerAId, $playerBId) {
            $q->where('player_a_id', $playerAId)
                ->where('player_b_id', $playerBId);
        })->orWhere(function ($q) use ($playerAId, $playerBId) {
            $q->where('player_a_id', $playerBId)
                ->where('player_b_id', $playerAId);
        });
    }
}
