<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 *
 *
 * @property string $puuid
 * @property string $name
 * @property string $tag
 * @property string $refreshed_at
 * @method static \Database\Factories\AccountFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account wherePuuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account whereRefreshedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account whereTag($value)
 * @mixin \Eloquent
 */
class Account extends Model
{
    use HasFactory;

    protected $primaryKey = 'puuid';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    public function summoner(): HasOne{
        return $this->hasOne(Summoner::class, 'account_id');
    }
}

