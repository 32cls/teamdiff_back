<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 *
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Participant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Participant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Participant query()
 * @mixin \Eloquent
 */
class Participant extends Model
{

    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'champion_id',
        'team_id',
        'team_position',
        'win',
        'kills',
        'deaths',
        'assists',
        'level',
    ];

    public function summoner(): BelongsTo
    {
        return $this->belongsTo(Summoner::class, 'summoner_id');
    }

    public function wrotereviews(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function receivedreviews(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewee_id');
    }

    public function lolmatch(): BelongsTo
    {
        return $this->belongsTo(LoLMatch::class, 'match_id');
    }
}
