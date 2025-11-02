<?php

use App\Modules\Market\Http\Controllers\MarketController;
use Illuminate\Support\Facades\Route;

Route::prefix('{{moduleName}}')->name('{{moduleName}}.')->group(function () {
    Route::get('/', [MarketController::class, 'hello'])->name('hello');
});