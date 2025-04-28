<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\LoLMatchFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoLMatch extends Model
{
    /** @use HasFactory<LoLMatchFactory> */
    use HasFactory;

    protected $keyType = 'string';

    protected $table = 'lolmatches';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'duration',
        'gameCreation',
    ];

    public function participations(): HasMany
    {
        return $this->hasMany(Participation::class, 'matchId');
    }

    protected function casts(): array
    {
        return [
            'gameCreation' => 'datetime:Y-m-d H:i:s',
        ];
    }
}
