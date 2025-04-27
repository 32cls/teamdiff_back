<?php

use App\Models\Account;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/auth/redirect', function () {
    return Socialite::driver('riot')->redirect();
});

Route::get('/auth/callback', function () {
    $user = Socialite::driver('riot')->user();

    $account = Account::updateOrCreate([
        'puuid' => $user->getId(),
    ], [
        'name' => $user->getNickname(),
        'refreshedAt' => now()
    ]);

    Auth::login($account);

});
