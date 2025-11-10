<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Dashboard\Http\Controllers\DashboardController;

Route::prefix('dashboard')->group(function () {
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/statistics', [DashboardController::class, 'statistics'])->name('dashboard.statistics');
    });
});
