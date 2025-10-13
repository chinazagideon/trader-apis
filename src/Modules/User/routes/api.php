<?php

use App\Modules\User\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| User Module API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register User module API routes for your application.
| These routes are loaded by the UserServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

Route::prefix('users')->name('users.')->group(function () {
    // Public routes (if any)
    Route::get('/health', [UserController::class, 'health'])->name('health');

    // Protected routes
    Route::middleware(['auth:sanctum'])->group(function () {
        // CRUD operations
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/stats', [UserController::class, 'stats'])->name('stats');
        Route::get('/search', [UserController::class, 'search'])->name('search');
        Route::get('/{id}', [UserController::class, 'show'])->name('show');
        Route::get('/uuid/{uuid}', [UserController::class, 'showByUuid'])->name('show.uuid');
        Route::put('/{id}', [UserController::class, 'update'])->name('update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
    });
});
