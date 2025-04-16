<?php
return [
    "apikey" => env("RIOT_API_KEY"),
    "queue" => env("RIOT_SOLO_QUEUE", 420),
    "matchesbatch" => env("RIOT_MATCH_BATCH", 5),
    "refreshlimit" => env("RIOT_REFRESH_LIMIT", 10),
];
