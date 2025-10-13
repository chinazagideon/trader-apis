<?php

use App\Modules\Investment\Http\Controllers\InvestmentController;
use Illuminate\Support\Facades\Route;

Route::prefix('investment')->name('investment.')->group(function () {
    // Health check endpoint (public)
    Route::get('/health', [InvestmentController::class, 'health'])->name('health');

    // Statistics endpoint (public)
    Route::get('/statistics', [InvestmentController::class, 'statistics'])->name('statistics');

    // CRUD operations (protected by authentication)
    Route::middleware(['auth:sanctum'])->group(function () {
        // Standard CRUD routes with Form Request validation
        Route::get('/', [InvestmentController::class, 'index'])->name('index');
        Route::post('/', [InvestmentController::class, 'store'])->name('store');
        Route::get('/{id}', [InvestmentController::class, 'show'])->name('show');
        Route::put('/{id}', [InvestmentController::class, 'update'])->name('update');
        Route::patch('/{id}', [InvestmentController::class, 'update'])->name('update.patch');
        Route::delete('/{id}', [InvestmentController::class, 'destroy'])->name('destroy');
    });
});
