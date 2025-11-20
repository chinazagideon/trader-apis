<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Client\Http\Controllers\ClientController;
use App\Modules\Client\Http\Controllers\ClientSecretController;

Route::prefix('client')->name('client.')->group(function () {
    Route::get('/health', [ClientController::class, 'health'])->name('health');
    Route::get('/scope', [ClientController::class, 'getClientScope'])->name('client.scope');
    Route::post('/activate', [ClientController::class, 'activateClient'])->name('client.activate');
    Route::post('/deactivate', [ClientController::class, 'deactivateClient'])->name('client.deactivate');

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/', [ClientController::class, 'index'])->name('index');
        Route::put('/config', [ClientController::class, 'ConfigUpdate'])->name('update.config');
        Route::post('/', [ClientController::class, 'store'])->name('store');
    });
});

Route::prefix('client-secret')->name('client-secret.')->group(function () {
    Route::get('/', [ClientSecretController::class, 'index'])->name('index');
    Route::post('/', [ClientSecretController::class, 'store'])->name('store');
    Route::put('/{clientSecret}', [ClientSecretController::class, 'update'])->name('update');
    Route::delete('/{clientSecret}', [ClientSecretController::class, 'destroy'])->name('destroy');
});
