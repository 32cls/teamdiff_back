<?php

use App\Http\Controller\AuthController;
use Illuminate\Support\Facades\Route;


Route::get('/', [AuthController::class, 'index'])->name('index');
Route::get('oauth2-callback', [AuthController::class, 'oauth2Callback'])->name('oauth2-callback');
