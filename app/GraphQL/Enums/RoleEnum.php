<?php

namespace App\GraphQL\Enums;

use Rebing\GraphQL\Support\EnumType;

class RoleEnum extends EnumType
{
    protected $attributes = [
        'name' => 'RoleEnum',
        'description' => 'The possible roles for a player',
        'values' => ['TOP', 'JUNGLE', 'MID', 'BOTTOM', 'SUPPORT'],
    ];
}
