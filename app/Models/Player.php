<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RoleEnum;
use App\Enums\TeamEnum;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Player extends Pivot
{
    use HasFactory;
    use HasTimestamps;
    use HasUlids;

    protected $table = 'lol_players';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [];

    public function summoner(): BelongsTo
    {
        return $this->belongsTo(Summoner::class, 'riot_summoner_id', 'riot_summoner_id');
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'riot_match_id', 'riot_match_id');
    }

    public function writtenReviews(): HasMany
    {
        return $this->hasMany(Review::class, 'author_id');
    }

    public function receivedReviews(): HasMany
    {
        return $this->hasMany(Review::class, 'subject_id');
    }

    protected function casts(): array
    {
        return [
            'riot_role' => RoleEnum::class,
            'riot_team' => TeamEnum::class,
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
