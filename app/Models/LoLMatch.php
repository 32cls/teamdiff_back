<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * 
 *
 * @property string $id
 * @property int $duration
 * @property \Illuminate\Support\Carbon $game_creation
 * @property-read \App\Models\Participant|null $pivot
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Summoner> $summoners
 * @property-read int|null $summoners_count
 * @method static \Database\Factories\LoLMatchFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoLMatch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoLMatch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoLMatch query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoLMatch whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoLMatch whereGameCreation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoLMatch whereId($value)
 * @mixin \Eloquent
 */
class LoLMatch extends Model
{
    /** @use HasFactory<\Database\Factories\LoLMatchFactory> */
    use HasFactory;
    protected $keyType = 'string';

    protected $table = 'lolmatches';

    public $timestamps = false;

    public function summoners(): BelongsToMany
    {
        return $this->belongsToMany(Summoner::class, 'participants', 'match_id', 'summoner_id')
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

    protected function casts(): array
    {
        return [
            'game_creation' => 'datetime:Y-m-d',
        ];
    }

}
