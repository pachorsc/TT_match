<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class News extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id',
        'headline',
        'summary',
        'source',
        'url',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function scopeLatest($query, int $limit = 10)
    {
        return $query->orderByDesc('published_at')->limit($limit);
    }
}
