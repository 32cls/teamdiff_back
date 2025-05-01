<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RegionEnum;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Summoner extends Model
{
    use HasFactory;
    use HasTimestamps;
    use HasUlids;

    protected $table = 'lol_summoners';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_puuid');
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class, 'riot_summoner_id');
    }

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(
            Game::class,
            'lol_players',
            'riot_summoner_id',
            'riot_game_id',
        )->using(Player::class);
    }

    protected function casts(): array
    {
        return [
            'region' => RegionEnum::class,
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
