<?php

use App\Modules\Role\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::prefix('{{moduleName}}')->name('{{moduleName}}.')->group(function () {
    Route::get('/', [RoleController::class, 'hello'])->name('hello');
});