<?php

use App\Modules\Auth\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->name('auth.')->group(function () {
    // Public routes
    Route::get('/health', [AuthController::class, 'health'])->name('health');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/password-reset-link', [AuthController::class, 'sendPasswordResetLink'])->name('password.reset.link');
    Route::post('/password-reset', [AuthController::class, 'resetPassword'])->name('password.reset');
    Route::post('/verify-email', [AuthController::class, 'verifyEmail'])->name('verify.email');

    // Test route without middleware
    Route::get('/test-auth', [AuthController::class, 'testAuth'])->name('test.auth');

    // Test route with middleware
    Route::middleware([\App\Core\Http\Middleware\AuthenticateApi::class . ':sanctum'])->get('/test-auth-protected', [AuthController::class, 'testAuth'])->name('test.auth.protected');

    // Protected routes
    Route::middleware([\App\Core\Http\Middleware\AuthenticateApi::class . ':sanctum'])->group(function () {
        Route::get('/me', [AuthController::class, 'me'])->name('me');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
        Route::post('/change-password', [AuthController::class, 'changePassword'])->name('change.password');
    });
});
