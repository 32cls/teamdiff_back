<?php

namespace App\Models;

use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    /** @use HasFactory<ReviewFactory> */
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'reviews';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'content',
        'rating',
        'revieweeId',
        'isAlly'
    ];

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Participation::class, 'reviewerId');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(Participation::class, 'receiverId');
    }

}
