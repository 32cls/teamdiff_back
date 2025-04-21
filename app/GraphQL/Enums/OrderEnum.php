<?php

namespace App\GraphQL\Enums;

use Rebing\GraphQL\Support\EnumType;

class OrderEnum extends EnumType
{
    protected $attributes = [
        'name' => 'OrderEnum',
        'description' => 'The possible values for ordering matches by game creation date',
        'values' => [
            'ASCENDING' => 'asc',
            'DESCENDING' => 'desc',
        ],
    ];
}
