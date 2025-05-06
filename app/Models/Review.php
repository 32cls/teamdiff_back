<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;
    use HasTimestamps;
    use HasUlids;

    protected $table = 'lol_reviews';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [];

    public function author(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'author_player_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'subject_player_id');
    }

    protected function casts(): array
    {
        return [
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
