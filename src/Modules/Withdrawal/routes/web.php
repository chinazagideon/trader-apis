<?php

use App\Modules\Withdrawal\Http\Controllers\WithdrawalController;
use Illuminate\Support\Facades\Route;

Route::prefix('{{moduleName}}')->name('{{moduleName}}.')->group(function () {
    Route::get('/', [WithdrawalController::class, 'hello'])->name('hello');
});