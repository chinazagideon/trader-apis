<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route alias for authentication middleware compatibility
Route::get('/login', function () {
    return response()->json([
        'message' => 'Please use /api/v1/auth/login for authentication',
        'login_url' => '/api/v1/auth/login'
    ], 401);
})->name('login');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
