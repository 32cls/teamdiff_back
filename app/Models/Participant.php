<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

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
