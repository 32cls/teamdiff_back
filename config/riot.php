<?php

declare(strict_types=1);

return [
    'api' => [
        'url' => env('RIOT_API_URL'),
        'key' => env('RIOT_API_KEY'),
    ],
    'queue' => env('RIOT_SOLO_QUEUE', 420),
    'matches_batch' => env('RIOT_MATCH_BATCH', 5),
    'refresh_limit' => env('RIOT_REFRESH_LIMIT', 10),
];
