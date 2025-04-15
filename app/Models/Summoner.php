<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 *
 *
 * @property int $id
 * @property int $icon
 * @property \Illuminate\Support\Carbon $revision_date
 * @property int $level
 * @property string $account_id
 * @property-read \App\Models\Account|null $account
 * @property-read \App\Models\Participant|null $pivot
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LoLMatch> $lolmatches
 * @property-read int|null $lolmatches_count
 * @method static \Database\Factories\SummonerFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Summoner newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Summoner newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Summoner query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Summoner whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Summoner whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Summoner whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Summoner whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Summoner whereRevisionDate($value)
 * @mixin \Eloquent
 */
class Summoner extends Model
{
    /** @use HasFactory<\Database\Factories\SummonerFactory> */
    use HasFactory;

    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'icon',
        'level',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'puuid');
    }

    public function lolmatches(): BelongsToMany
    {
        return $this->belongsToMany(LolMatch::class, 'participants', 'summoner_id', 'match_id')
            ->using(Participant::class)
            ->withPivot([
                'champion_id',
                'team_id',
                'team_position',
                'win',
                'kills',
                'deaths',
                'assists',
                'level'
            ]);
    }

}
