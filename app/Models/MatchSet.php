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
        return $this->belongsTo(GameMatch::class, 'match_id');
    }
}
