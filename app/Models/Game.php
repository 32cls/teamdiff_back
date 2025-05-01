<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    use HasTimestamps;
    use HasUlids;

    protected $table = 'lol_games';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [];

    public function player(): HasMany
    {
        return $this->hasMany(Player::class, 'riot_game_id');
    }

    public function summoners(): BelongsToMany
    {
        return $this->belongsToMany(
            Summoner::class,
            'lol_players',
            'riot_game_id',
            'riot_summoner_id',
        )->using(Player::class);
    }

    protected function casts(): array
    {
        return [
            'started_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
