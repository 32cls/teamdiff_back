<?php

declare(strict_types=1);

use App\Http\Controllers\RiotSocialiteController;

Route::prefix('auth')->group(function () {
    Route::get('redirect', [RiotSocialiteController::class, 'redirect'])->name('auth.redirect');
    Route::get('callback', [RiotSocialiteController::class, 'callback'])->name('auth.callback');
});
