<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LoLMatch extends Model
{
    /** @use HasFactory<\Database\Factories\LoLMatchFactory> */
    use HasFactory;
    protected $keyType = 'string';

    protected $table = 'lolmatches';

    public $timestamps = false;

    public function summoners(): BelongsToMany
    {
        return $this->belongsToMany(Summoner::class, 'participants', 'match_id', 'summoner_id')->using(Participant::class);
    }

}
