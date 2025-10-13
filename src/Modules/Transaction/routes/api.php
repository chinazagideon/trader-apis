<?php

use App\Modules\Transaction\Http\Controllers\TransactionCategoryController;
use App\Modules\Transaction\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::prefix('transaction')->name('transaction.')->group(function () {
    Route::get('/health', [TransactionController::class, 'health'])->name('health');

    Route::middleware(['auth:sanctum'])->group(function () {
        // Category routes MUST come before transaction routes to avoid conflicts
        Route::prefix('category')->name('category.')->group(function () {
            Route::get('/', [TransactionCategoryController::class, 'index'])->name('index');
            Route::post('/', [TransactionCategoryController::class, 'store'])->name('store');
            Route::get('/{id}', [TransactionCategoryController::class, 'show'])->name('show');
            Route::put('/{id}', [TransactionCategoryController::class, 'update'])->name('update');
            Route::delete('/{id}', [TransactionCategoryController::class, 'destroy'])->name('destroy');
        });

        // Transaction routes come after category routes
        Route::get('/', [TransactionController::class, 'index'])->name('index');
        Route::post('/', [TransactionController::class, 'store'])->name('store');
        Route::get('/{id}', [TransactionController::class, 'show'])->name('show');
        Route::put('/{id}', [TransactionController::class, 'update'])->name('update');
        Route::delete('/{id}', [TransactionController::class, 'destroy'])->name('destroy');
    });
});
