<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\TestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Тестовый маршрут для проверки
Route::get('/test', TestController::class);

// Публичные маршруты (без аутентификации)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Email verification маршруты
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['auth:sanctum', 'signed'])
    ->name('verification.verify');

// Защищенные маршруты (с аутентификацией Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [UserController::class, 'show']);

    // Email verification маршруты (требуют аутентификацию)
    Route::post('/email/verification-notification', [AuthController::class, 'sendEmailVerification'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
    Route::post('/email/verify/resend', [AuthController::class, 'resendEmailVerification'])
        ->middleware('throttle:6,1');
});
