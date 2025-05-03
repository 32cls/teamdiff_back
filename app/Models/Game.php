<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\WeakEnum;
use App\Enums\TeamEnum;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property TeamEnum|string $winning_riot_team_id
 */
class Game extends Model
{
    use HasFactory;
    use HasTimestamps;
    use HasUlids;

    protected $table = 'lol_games';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [];

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function summoners(): BelongsToMany
    {
        return $this->belongsToMany(Summoner::class, 'lol_players')->using(Player::class);
    }

    protected function casts(): array
    {
        return [
            'winning_riot_team_id' => WeakEnum::of(TeamEnum::class),
            'started_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
