<?php

use App\Modules\Notification\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('{{moduleName}}')->name('{{moduleName}}.')->group(function () {
    Route::get('/', [NotificationController::class, 'hello'])->name('hello');
});