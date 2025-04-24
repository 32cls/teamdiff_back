<?php

use App\Http\Controller\AuthController;
use Illuminate\Support\Facades\Route;


Route::get('/', [AuthController::class, 'index'])->name('index');
