<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Model implements AuthenticatableContract
{
    use Authenticatable;
    use HasFactory;
    use HasTimestamps;
    use HasUlids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [];

    protected $hidden = ['riot_puuid', 'access_token', 'refresh_token'];

    public function summoner(): HasOne
    {
        return $this->hasOne(Summoner::class);
    }

    protected function casts(): array
    {
        return [
            'refreshed_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
