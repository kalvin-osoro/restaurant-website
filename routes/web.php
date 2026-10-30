<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json([
    'name' => config('app.name'),
    'status' => 'ok',
]));

Route::get('/login', [\App\Http\Controllers\OAuthSessionController::class, 'show'])->name('login');
Route::post('/login', [\App\Http\Controllers\OAuthSessionController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
Route::post('/logout', [\App\Http\Controllers\OAuthSessionController::class, 'destroy'])->middleware('auth:web')->name('session.logout');
