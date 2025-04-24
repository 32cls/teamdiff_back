<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Participation extends Model
{

    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'championId',
        'teamId',
        'role',
        'win',
        'kills',
        'deaths',
        'assists',
        'level',
    ];

    public function summoner(): BelongsTo
    {
        return $this->belongsTo(Summoner::class, 'summonerId');
    }

    public function writtenReviews(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewerId');
    }

    public function receivedReviews(): HasMany
    {
        return $this->hasMany(Review::class, 'receiverId');
    }

    public function lolMatch(): BelongsTo
    {
        return $this->belongsTo(LoLMatch::class, 'matchId');
    }
}
