<?php

use App\Modules\Currency\Http\Controllers\CurrencyController;
use Illuminate\Support\Facades\Route;

Route::prefix('currency')->name('currency.')->group(function () {
    Route::get('/health', [CurrencyController::class, 'health'])->name('health');

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/', [CurrencyController::class, 'index'])->name('index');
        Route::post('/', [CurrencyController::class, 'store'])->name('store');
        Route::get('/{id}', [CurrencyController::class, 'show'])->name('show');
        Route::put('/{id}', [CurrencyController::class, 'update'])->name('update');
        Route::delete('/{id}', [CurrencyController::class, 'destroy'])->name('destroy');
    });
});
