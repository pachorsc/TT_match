<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Ranking extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'player_id',
        'ranking',
        'rating_points',
        'ranking_date',
    ];

    protected $casts = [
        'ranking' => 'integer',
        'rating_points' => 'integer',
        'ranking_date' => 'date',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}
