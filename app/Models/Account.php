<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Scout\Builder;
use Laravel\Scout\Searchable;

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
        return $this->hasOne(Summoner::class, 'accountPuuid');
    }

    protected $fillable = [
        'puuid',
        'name',
        'tag',
        'refreshedAt',
        'region'
    ];

    protected function casts(): array
    {
        return [
            'refreshedAt' => 'datetime:Y-m-d H:i:s',
        ];
    }

    public function toSearchableArray(): array
    {
       return [
           'name' => $this->name,
           'tag' => $this->tag,
           'summonerIcon' => $this->summoner->icon ?? null,
           'region' => $this->region
       ];
    }

    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with('summoner');
    }
}

