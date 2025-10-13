<?php

use App\Modules\Pricing\Http\Controllers\PricingController;
use Illuminate\Support\Facades\Route;

Route::prefix('{{moduleName}}')->name('{{moduleName}}.')->group(function () {
    Route::get('/', [PricingController::class, 'index'])->name('index');
    Route::get('/create', [PricingController::class, 'create'])->name('create');
    Route::post('/', [PricingController::class, 'store'])->name('store');
    Route::get('/{id}', [PricingController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [PricingController::class, 'edit'])->name('edit');
    Route::put('/{id}', [PricingController::class, 'update'])->name('update');
    Route::delete('/{id}', [PricingController::class, 'destroy'])->name('destroy');
});