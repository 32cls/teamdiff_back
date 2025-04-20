<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Scout\Builder;
use Laravel\Scout\Searchable;

/**
 *
 *
 * @property string $puuid
 * @property string $name
 * @property string $tag
 * @property \Illuminate\Support\Carbon $refreshed_at
 * @property-read \App\Models\Summoner|null $summoner
 * @method static \Database\Factories\AccountFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account wherePuuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account whereRefreshedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account whereTag($value)
 * @mixin \Eloquent
 */
class Account extends Model
{

    use HasFactory;
    use Searchable;

    protected $primaryKey = 'puuid';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    public function summoner(): HasOne
    {
        return $this->hasOne(Summoner::class, 'account_id');
    }

    protected $fillable = [
        'puuid',
        'name',
        'tag',
        'refreshed_at',
    ];

    protected function casts(): array
    {
        return [
            'refreshed_at' => 'datetime:Y-m-d H:i:s',
        ];
    }

    public function toSearchableArray(): array
    {
       return [
           'name' => $this->name,
           'tag' => $this->tag,
           'summoner_icon' => $this->summoner->icon ?? null
       ];
    }

    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with('summoner');
    }
}

