<?php

namespace App\GraphQL\Enums;

use Rebing\GraphQL\Support\EnumType;

class OrderEnum extends EnumType
{
    protected $attributes = [
        'name' => 'OrderEnum',
        'description' => 'The possible values for orders',
        'values' => [
            'ASCENDING' => 'asc',
            'DESCENDING' => 'desc',
        ],
    ];
}
