<?php

use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json([
    'name' => config('app.name'),
    'status' => 'ok',
]))->name('api.health');

Route::post('/consultations', [\App\Http\Controllers\ConsultationController::class, 'store'])->middleware('throttle:5,1')->name('api.consultations.store');

Route::middleware('throttle:60,1')->group(function () {
    $controller = \App\Http\Controllers\PublicContentController::class;
    Route::get('/attorneys', [$controller, 'attorneys']);
    Route::get('/attorneys/{slug}', [$controller, 'attorney'])->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*');
    Route::get('/practice-areas', [$controller, 'practiceAreas']);
    Route::get('/cases', [$controller, 'cases']);
    Route::get('/cases/{slug}', [$controller, 'caseStudy'])->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*');
    Route::get('/case-categories', [$controller, 'caseCategories']);
});
