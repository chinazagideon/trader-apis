<?php

use App\Modules\Payment\Http\Controllers\PaymentController;
use App\Modules\Payment\Http\Controllers\PaymentGatewayController;
use App\Modules\Payment\Http\Controllers\PaymentProcessorController;
use Illuminate\Support\Facades\Route;

Route::prefix('payment')->name('payment.')->group(function () {
    Route::get('/health', [PaymentController::class, 'health'])->name('health');

    Route::middleware(['auth:sanctum'])->group(function () {

        Route::prefix('processor')->name('processor.')->group(function() {
            Route::post('/initiate', [PaymentProcessorController::class, 'Initiate'])->name('postInitiate');
            Route::get('/', [PaymentProcessorController::class, 'index'])->name('show');
        });

        Route::prefix('gateway')->name('gateway.')->group(function () {
            Route::get('/slug', [PaymentGatewayController::class, 'getPaymentGatewayBySlug'])->name('getPaymentGatewayBySlug');

            Route::get('/', [PaymentGatewayController::class, 'index'])->name('index');
            Route::post('/', [PaymentGatewayController::class, 'store'])->name('store');
            Route::get('/{id}', [PaymentGatewayController::class, 'show'])->name('show');
            Route::put('/{id}', [PaymentGatewayController::class, 'update'])->name('update');
            Route::delete('/{id}', [PaymentGatewayController::class, 'destroy'])->name('destroy');
        });
        Route::get('/', [PaymentController::class, 'index'])->name('index');
        Route::post('/', [PaymentController::class, 'store'])->name('store');
        Route::get('/{id}', [PaymentController::class, 'show'])->name('show');
        Route::put('/{id}', [PaymentController::class, 'update'])->name('update');
        Route::delete('/{id}', [PaymentController::class, 'destroy'])->name('destroy');
    });
});
