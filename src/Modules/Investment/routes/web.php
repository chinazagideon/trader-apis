<?php

use App\Modules\Investment\Http\Controllers\InvestmentController;
use Illuminate\Support\Facades\Route;

Route::prefix('{{moduleName}}')->name('{{moduleName}}.')->group(function () {
    Route::get('/', [InvestmentController::class, 'index'])->name('index');
    Route::get('/create', [InvestmentController::class, 'create'])->name('create');
    Route::post('/', [InvestmentController::class, 'store'])->name('store');
    Route::get('/{id}', [InvestmentController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [InvestmentController::class, 'edit'])->name('edit');
    Route::put('/{id}', [InvestmentController::class, 'update'])->name('update');
    Route::delete('/{id}', [InvestmentController::class, 'destroy'])->name('destroy');
});