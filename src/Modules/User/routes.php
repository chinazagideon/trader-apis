<?php

use App\Modules\User\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| User Module Routes
|--------------------------------------------------------------------------
|
| Here is where you can register User module routes for your application.
| These routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

Route::prefix('api/v1/users')->group(function () {
    // CRUD operations
    Route::get('/', [UserController::class, 'index'])->name('users.index');
    Route::post('/', [UserController::class, 'store'])->name('users.store');
    Route::get('/stats', [UserController::class, 'stats'])->name('users.stats');
    Route::get('/search', [UserController::class, 'search'])->name('users.search');
    Route::get('/{id}', [UserController::class, 'show'])->name('users.show');
    Route::get('/uuid/{uuid}', [UserController::class, 'showByUuid'])->name('users.show.uuid');
    Route::put('/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/{id}', [UserController::class, 'destroy'])->name('users.destroy');
});
