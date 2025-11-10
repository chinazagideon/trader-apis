<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Client\Http\Controllers\ClientController;


Route::prefix('client')->name('client.')->group(function () {
    Route::get('/health', [ClientController::class, 'health'])->name('health');
    Route::get('/scope', [ClientController::class, 'getClientScope'])->name('client.scope');
});
