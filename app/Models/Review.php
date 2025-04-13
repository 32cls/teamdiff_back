<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    /** @use HasFactory<\Database\Factories\ReviewFactory> */
    use HasFactory;

    public function author(): BelongsTo
    {
        return $this->belongsTo(Summoner::class);
    }

    public function reviewed(): BelongsTo
    {
        return $this->belongsTo(Summoner::class);
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(LoLMatch::class);
    }
}
