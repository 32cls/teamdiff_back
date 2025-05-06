<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\WeakEnum;
use App\Enums\RoleEnum;
use App\Enums\TeamEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property RoleEnum|string $riot_role
 * @property TeamEnum|string $riot_team_id
 */
class Player extends Pivot
{
    use HasFactory;
    use HasTimestamps;
    use HasUlids;

    protected $table = 'lol_players';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [];

    protected $appends = ['has_won'];

    public function summoner(): BelongsTo
    {
        return $this->belongsTo(Summoner::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    protected function hasWon(): Attribute
    {
        return Attribute::get(function () {
            $winningTeam = $this->game()->first()->winning_riot_team_id;
            if (isset($this->riot_team_id, $winningTeam)) {
                return $this->riot_team_id == $winningTeam;
            }

            return null;
        })->shouldCache();
    }

    public function authoredReviews(): HasMany
    {
        return $this->hasMany(Review::class, 'author_player_id');
    }

    public function subjectedToReviews(): HasMany
    {
        return $this->hasMany(Review::class, 'subject_player_id');
    }

    protected function casts(): array
    {
        return [
            'riot_role' => WeakEnum::of(RoleEnum::class),
            'riot_team_id' => WeakEnum::of(TeamEnum::class),
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
