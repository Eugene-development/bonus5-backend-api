<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\TestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Тестовый маршрут для проверки
Route::get('/test', TestController::class);

// Note: Login/register routes moved to web.php for stateful authentication

// Email verification маршруты
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['auth', 'signed'])
    ->name('verification.verify');


// Note: Protected routes moved to web.php for SPA stateful authentication
