<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Log Viewer Routes - Only accessible in development/local environment
if (app()->environment(['local', 'development', 'dev'])) {
    Route::middleware(['web', \App\Core\Http\Middleware\LogViewerMiddleware::class])
        ->prefix('logs')
        ->group(function () {
            Route::get('/', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index')->name('logs.index');
            Route::get('/{file}', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@show')->name('logs.show');
            Route::get('/{file}/download', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@download')->name('logs.download');
            Route::delete('/{file}', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@delete')->name('logs.delete');
        });
}
