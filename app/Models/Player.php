<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Player extends Model
{
    use HasFactory;

    protected $fillable = [
        'wtt_id',
        'ittf_id',
        'first_name',
        'last_name',
        'gender',
        'country',
        'country_code',
        'date_of_birth',
        'height_cm',
        'dominant_hand',
        'playing_style',
        'world_ranking',
        'rating_points',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'height_cm' => 'integer',
        'world_ranking' => 'integer',
        'rating_points' => 'integer',
    ];

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function rankings(): HasMany
    {
        return $this->hasMany(Ranking::class);
    }
}
