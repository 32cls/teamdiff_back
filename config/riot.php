<?php
return [
    "apiKey" => env("RIOT_API_KEY"),
    "queue" => env("RIOT_SOLO_QUEUE", 420),
    "matchesBatch" => env("RIOT_MATCH_BATCH", 5),
    "refreshLimit" => env("RIOT_REFRESH_LIMIT", 10),
    "clientId" => env("RIOT_CLIENT_ID", "XXX"),
    "clientSecret" => env("RIOT_CLIENT_SECRET", "XXX"),
    "provider" => env("RIOT_PROVIDER", "https://auth.riotgames.com"),
];
