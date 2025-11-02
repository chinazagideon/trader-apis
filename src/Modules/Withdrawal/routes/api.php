<?php

use App\Modules\Withdrawal\Http\Controllers\WithdrawalController;
use Illuminate\Support\Facades\Route;

Route::prefix('withdrawal')->name('withdrawal.')->group(function () {
    Route::get('/health', [WithdrawalController::class, 'health'])->name('health');

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/', [WithdrawalController::class, 'index'])->name('index');
        Route::post('/', [WithdrawalController::class, 'store'])->name('store');
        Route::get('/{id}', [WithdrawalController::class, 'show'])->name('show');
        Route::put('/{id}', [WithdrawalController::class, 'update'])->name('update');
        Route::delete('/{id}', [WithdrawalController::class, 'destroy'])->name('destroy');

        Route::put('/{id}/cancel', [WithdrawalController::class, 'cancel'])->name('cancel');
        Route::put('/{id}/complete', [WithdrawalController::class, 'complete'])->name('complete');
    });
});
