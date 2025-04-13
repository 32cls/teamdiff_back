<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * 
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Participant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Participant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Participant query()
 * @mixin \Eloquent
 */
class Participant extends Pivot
{
    protected $fillable = [
        'summoner_id',
        'match_id',
        'champion_id',
        'team_id',
        'team_position',
        'win',
        'kills',
        'deaths',
        'assists',
        'level',
    ];
}
