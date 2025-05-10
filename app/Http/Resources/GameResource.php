<?php

namespace App\Http\Resources;

use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource
{
    public function toArray(Request $request)
    {
        /** @var Game $game */
        $game = $this->resource;

        return [
            "riot_match_id" => $game->riot_match_id,
            "started_at" => $game->started_at
        ];

    }
}
