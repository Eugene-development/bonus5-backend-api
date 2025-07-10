<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-db', function () {
    try {
        DB::connection()->getPdo();
        return 'База данных подключена!!!';
    } catch (\Exception $e) {
        return 'Unable to connect to the database: ' . $e->getMessage();
    }
});

// Email verification routes for SPA
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmailWeb'])
    ->middleware(['signed'])
    ->name('verification.verify');

Route::get('/email/verified', function () {
    return view('email-verified');
})->name('verification.success');

Route::get('/email/verification-failed', function () {
    return view('email-verification-failed');
})->name('verification.failed');

// Note: Registration now handled via /api/register endpoint

// Note: Debug routes removed after successful SPA authentication fix

// SPA API endpoints in web routes for stateful authentication
Route::prefix('api')->middleware(['web'])->group(function () {
    // Public routes (for stateful authentication)
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/registration', [AuthController::class, 'register']); // Alias for frontend compatibility
    Route::post('/login', [AuthController::class, 'login']);

    // Protected routes
    Route::middleware(['auth'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [UserController::class, 'show']);

        // Email verification routes (require authentication)
        Route::post('/email/verification-notification', [AuthController::class, 'sendEmailVerification'])
            ->middleware('throttle:6,1')
            ->name('verification.send');
        Route::post('/email/verify/resend', [AuthController::class, 'resendEmailVerification'])
            ->middleware('throttle:6,1');
    });
});
