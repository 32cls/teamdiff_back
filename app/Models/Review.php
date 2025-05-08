<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use HasFactory;
    use HasTimestamps;
    use HasUlids;
    use SoftDeletes;

    protected $table = 'lol_reviews';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['content', 'rating'];

    protected $appends = ['is_from_ally'];

    public function author(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'author_player_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'subject_player_id');
    }

    protected function isFromAlly(): Attribute
    {
        return Attribute::get(function () {
            return Player::query()
                ->whereIn('id', [$this->author_player_id, $this->subject_player_id])
                ->selectRaw('count(distinct riot_team_id) = 1 as is_same')
                ->value('is_same');
        })->shouldCache();
    }

    protected function casts(): array
    {
        return [
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
