<?php

namespace App\Models;

use Database\Factories\SummonerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Summoner extends Model
{
    /** @use HasFactory<SummonerFactory> */
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
        return $this->belongsTo(Account::class, 'accountId');
    }

    public function participations(): HasMany
    {
        return $this->hasMany(Participation::class, 'summonerId');
    }

}
