<?php

use App\Modules\Swap\Http\Controllers\SwapController;
use Illuminate\Support\Facades\Route;

Route::prefix('{{moduleName}}')->name('{{moduleName}}.')->group(function () {
    Route::get('/', [SwapController::class, 'hello'])->name('hello');
});