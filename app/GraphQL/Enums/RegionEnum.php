<?php

namespace App\GraphQL\Enums;

use Rebing\GraphQL\Support\EnumType;

class RegionEnum extends EnumType
{
    protected $attributes = [
        'name' => 'RegionEnum',
        'description' => 'The possible values for regions',
        'values' => [
            'NA1',
            'BR1',
            'LA1',
            'LA2',
            'KR',
            'JP1',
            'EUN1',
            'EUW1',
            'ME1',
            'TR1',
            'RU',
            'OC1',
            'SG2',
            'TW2',
            'VN2'
        ],
    ];
}
