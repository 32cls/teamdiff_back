<?php

declare(strict_types=1);

use App\Http\Controllers\ReviewController;
use App\Http\Controllers\RiotSocialiteController;
use App\Http\Controllers\UserController;

Route::prefix('auth')
    ->as('auth.')
    ->group(function () {
        Route::get('redirect', [RiotSocialiteController::class, 'redirect'])
            ->name('redirect');
        Route::get('callback', [RiotSocialiteController::class, 'callback'])
            ->name('callback');
    });

Route::prefix('users')
    ->as('users.')
    ->middleware('auth')
    ->group(function (){
        Route::get('me', [UserController::class, 'me'])
            ->name('show-me');
        Route::get('{region}/{name}/{tag}', [UserController::class, 'byRegionAndNameTag'])
            ->name('show-on-by-region-and-nametag');

        Route::prefix('{user}')
            ->as('reviews.')
            ->group(function () {
                Route::get('reviews-as-author', [ReviewController::class, 'indexForUserAsAuthor'])
                    ->name('index-as-author');
                Route::get('reviews-as-subject', [ReviewController::class, 'indexForUserAsSubject'])
                    ->name('index-as-subject');
                Route::post('reviews', [ReviewController::class, 'storeForUser'])
                    ->name('store-for-user');
            });
    });

