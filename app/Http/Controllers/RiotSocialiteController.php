<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;

class RiotSocialiteController
{
    public function redirect()
    {
        return Socialite::driver('riot')->stateless()->redirect();
    }

    public function callback()
    {
        $rawUser = Socialite::driver('riot')->stateless()->user();

        return json_encode($rawUser, JSON_PRETTY_PRINT);
    }
}
