<?php

use App\Modules\Swap\Http\Controllers\SwapController;
use App\Modules\Swap\Http\Controllers\SwapRateHistoryController;
use Illuminate\Support\Facades\Route;

Route::prefix('swap')->name('swap.')->group(function () {
    Route::get('/health', [SwapController::class, 'health'])->name('health');


    Route::prefix('swap-rate-history')->name('swap-rate-history.')->group(function () {
        Route::get('/', [SwapRateHistoryController::class, 'index'])->name('index');
        Route::post('/', [SwapRateHistoryController::class, 'store'])->name('store');
        Route::get('/{id}', [SwapRateHistoryController::class, 'show'])->name('show');
        Route::put('/{id}', [SwapRateHistoryController::class, 'update'])->name('update');
        Route::delete('/{id}', [SwapRateHistoryController::class, 'destroy'])->name('destroy');
    });
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/', [SwapController::class, 'index'])->name('index');
        Route::post('/', [SwapController::class, 'store'])->name('store');
        Route::get('/{id}', [SwapController::class, 'show'])->name('show');
        Route::put('/{id}', [SwapController::class, 'update'])->name('update');
        Route::delete('/{id}', [SwapController::class, 'destroy'])->name('destroy');
    });

});
