<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\RegionEnum as Region;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class UserController
{
    public function me(): JsonResource
    {
        return auth()->user()->toResource();
    }

    public function byRegionAndNameTag(Region $region, string $name, string $tag)
    {
        return User::whereRelation('summoner', 'region', '=', $region)
            ->where('name', $name)
            ->where('tag', $tag)
            ->firstOrFail()
            ->toResource();
    }
}
