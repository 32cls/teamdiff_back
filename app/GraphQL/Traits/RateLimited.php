<?php

namespace App\GraphQL\Traits;

use GraphQL\Error\Error;
use Illuminate\Support\Facades\RateLimiter;

trait RateLimited
{
    /**
     * @throws Error
     */
    protected function enforceRateLimit(string $name, int $maxAttempts = 10, int $decaySeconds = 60): void
    {
        $ip = request()->ip();
        $key = "graphql:$ip:$name";

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            throw new Error("Rate limit reached for $name. Try again in $seconds seconds.");
        }

        RateLimiter::hit($key, $decaySeconds);
    }
}
