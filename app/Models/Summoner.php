<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RegionEnum;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Laravel\Scout\Searchable;

class Summoner extends Model
{
    use HasFactory;
    use HasTimestamps;
    use HasUlids;
    use Searchable;

    protected $table = 'lol_summoners';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'lol_players')->using(Player::class);
    }

    public function writtenReviews(): HasManyThrough
    {
        return $this->hasManyThrough(
            Review::class,
            Player::class,
            secondKey: 'author_player_id',
        );
    }

    public function receivedReviews(): HasManyThrough
    {
        return $this->hasManyThrough(
            Review::class,
            Player::class,
            secondKey: 'subject_player_id',
        );
    }

    protected function casts(): array
    {
        return [
            'region' => RegionEnum::class,
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    public function toSearchableArray(): array
    {
        return [
            'name' => $this->user->name,
            'tag' => $this->user->tag,
            'summoner_icon' => $this->icon_id,
            'region' => $this->region->value,
        ];
    }

    protected function makeAllSearchableUsing(EloquentBuilder $query): EloquentBuilder
    {
        return $query->with('user');
    }
}
