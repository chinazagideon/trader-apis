<?php

use App\Modules\Market\Http\Controllers\MarketController;
use App\Modules\Market\Http\Controllers\MarketPriceController;
use Illuminate\Support\Facades\Route;

Route::prefix('market')->name('market.')->group(function () {
    Route::get('/health', [MarketController::class, 'health'])->name('health');

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::prefix('price')->name('price.')->group(function () {
            Route::get('/{symbol}', [MarketPriceController::class, 'getCurrencyPriceBySymbol'])->name('getCurrencyPriceBySymbol');
            Route::get('/', [MarketPriceController::class, 'index'])->name('index');
            Route::post('/', [MarketPriceController::class, 'store'])->name('store');
            Route::get('/{id}', [MarketPriceController::class, 'show'])->name('show');
            Route::put('/{id}', [MarketPriceController::class, 'update'])->name('update');
            Route::delete('/{id}', [MarketPriceController::class, 'destroy'])->name('destroy');

        });
        Route::get('/', [MarketController::class, 'index'])->name('index');
        Route::post('/', [MarketController::class, 'store'])->name('store');
        Route::get('/{id}', [MarketController::class, 'show'])->name('show');
        Route::put('/{id}', [MarketController::class, 'update'])->name('update');
        Route::delete('/{id}', [MarketController::class, 'destroy'])->name('destroy');
    });
});
