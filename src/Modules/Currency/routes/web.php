<?php

use App\Modules\Currency\Http\Controllers\CurrencyController;
use Illuminate\Support\Facades\Route;

Route::prefix('{{moduleName}}')->name('{{moduleName}}.')->group(function () {
    Route::get('/', [CurrencyController::class, 'index'])->name('index');
    Route::get('/create', [CurrencyController::class, 'create'])->name('create');
    Route::post('/', [CurrencyController::class, 'store'])->name('store');
    Route::get('/{id}', [CurrencyController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [CurrencyController::class, 'edit'])->name('edit');
    Route::put('/{id}', [CurrencyController::class, 'update'])->name('update');
    Route::delete('/{id}', [CurrencyController::class, 'destroy'])->name('destroy');
});