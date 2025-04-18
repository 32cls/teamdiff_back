<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 *
 *
 * @property string $id
 * @property int $duration
 * @property \Illuminate\Support\Carbon $game_creation
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

    protected $fillable = [
        'id',
        'duration',
        'game_creation'
    ];

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class, 'match_id');
    }

    protected function casts(): array
    {
        return [
            'game_creation' => 'datetime:Y-m-d H:i:s',
        ];
    }

}
