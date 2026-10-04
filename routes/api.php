<?php

use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json([
    'name' => config('app.name'),
    'status' => 'ok',
]))->name('api.health');

Route::post('/consultations', [\App\Http\Controllers\ConsultationController::class, 'store'])->middleware('throttle:5,1')->name('api.consultations.store');
