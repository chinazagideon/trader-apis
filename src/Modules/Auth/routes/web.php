<?php

use App\Modules\Auth\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('{{moduleName}}')->name('{{moduleName}}.')->group(function () {
    Route::get('/', [AuthController::class, 'index'])->name('index');
    Route::get('/create', [AuthController::class, 'create'])->name('create');
    Route::post('/', [AuthController::class, 'store'])->name('store');
    Route::get('/{id}', [AuthController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [AuthController::class, 'edit'])->name('edit');
    Route::put('/{id}', [AuthController::class, 'update'])->name('update');
    Route::delete('/{id}', [AuthController::class, 'destroy'])->name('destroy');
});