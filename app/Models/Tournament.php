<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Tournament extends Model
{
    use HasFactory;

    protected $fillable = [
        'statstt_id',
        'name',
        'location',
        'country',
        'country_code',
        'start_date',
        'end_date',
        'category',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function matches(): HasMany
    {
        return $this->hasMany(GameMatch::class);
    }
}
