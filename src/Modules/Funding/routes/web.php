<?php

use App\Modules\Funding\Http\Controllers\FundingController;
use Illuminate\Support\Facades\Route;

Route::prefix('{{moduleName}}')->name('{{moduleName}}.')->group(function () {
    Route::get('/', [FundingController::class, 'hello'])->name('hello');
});