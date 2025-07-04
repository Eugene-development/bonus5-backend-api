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
Route::post('/registration', [AuthController::class, 'register']); // Алиас для совместимости с фронтендом
Route::post('/login', [AuthController::class, 'login']);

// Email verification маршруты
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['auth', 'signed'])
    ->name('verification.verify');

// Note: Protected routes moved to web.php for SPA stateful authentication
