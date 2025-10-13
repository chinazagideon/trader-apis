<?php

use App\Modules\Pricing\Http\Controllers\PricingController;
use Illuminate\Support\Facades\Route;

Route::prefix('pricing')->name('pricing.')->group(function () {
    Route::get('/health', [PricingController::class, 'health'])->name('health');

    Route::middleware('api')->group(function () {
        Route::get('/', [PricingController::class, 'index'])->name('index');
        Route::post('/', [PricingController::class, 'store'])->name('store');
        Route::get('/{id}', [PricingController::class, 'show'])->name('show');
        Route::put('/{id}', [PricingController::class, 'update'])->name('update');
        Route::delete('/{id}', [PricingController::class, 'destroy'])->name('destroy');
    });
});
