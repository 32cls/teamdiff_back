<?php

use App\Http\Controllers\ReviewController;
use Laravel\Socialite\Facades\Socialite;

Route::get('/auth/redirect', function () {
    return Socialite::driver('riot')->stateless()->redirect();
});

Route::get('/auth/callback', function () {
    $user = Socialite::driver('riot')->stateless()->user();

    $account = Account::updateOrCreate([
        'puuid' => $user->getId(),
    ], [
        'name' => $user->getNickname(),
        'tag' => explode("#", $user->getName())[1],
        'refreshedAt' => now()
    ]);

});

Route::apiResource('reviews', ReviewController::class);


