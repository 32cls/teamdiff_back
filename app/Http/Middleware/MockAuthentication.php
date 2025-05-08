<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class MockAuthentication
{
    public function handle(Request $request, Closure $next)
    {
        if (config('auth.should_mock') && ! app()->isProduction()) {
            if ($request->filled('mock_user_id')) {
                $user = User::findOrFail($request->input('mock_user_id'));
            }

            $user ??= User::first();
            auth()->login($user);

            $request->offsetUnset('mock_user_id');
        }

        return $next($request);
    }
}
