<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Summoner extends Model
{
    /** @use HasFactory<\Database\Factories\SummonerFactory> */
    use HasFactory;

    public $timestamps = false;

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
