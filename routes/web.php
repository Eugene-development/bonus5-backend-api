<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

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
