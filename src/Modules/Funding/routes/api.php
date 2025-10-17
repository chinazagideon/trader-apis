<?php

use App\Modules\Funding\Http\Controllers\FundingController;
use Illuminate\Support\Facades\Route;

Route::prefix('funding')->name('funding.')->group(function () {
    Route::get('/health', [FundingController::class, 'health'])->name('health');

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/', [FundingController::class, 'index'])->name('index');
        Route::post('/', [FundingController::class, 'store'])->name('store');
        Route::get('/{id}', [FundingController::class, 'show'])->name('show');
        Route::put('/{id}', [FundingController::class, 'update'])->name('update');
        Route::delete('/{id}', [FundingController::class, 'destroy'])->name('destroy');
    });
});
