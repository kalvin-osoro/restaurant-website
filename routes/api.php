<?php

use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json([
    'name' => config('app.name'),
    'status' => 'ok',
]))->name('api.health');

Route::post('/consultations', [\App\Http\Controllers\ConsultationController::class, 'store'])->middleware('throttle:5,1')->name('api.consultations.store');

Route::middleware('throttle:60,1')->group(function () {
    $controller = \App\Http\Controllers\PublicContentController::class;
    Route::get('/team', [$controller, 'attorneys'])->name('api.team.index');
    Route::get('/team/{slug}', [$controller, 'attorney'])->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')->name('api.team.show');
    Route::get('/attorneys', [$controller, 'attorneys']);
    Route::get('/attorneys/{slug}', [$controller, 'attorney'])->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*');
    Route::get('/practice-areas', [$controller, 'practiceAreas']);
    Route::get('/cases', [$controller, 'cases']);
    Route::get('/cases/{slug}', [$controller, 'caseStudy'])->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*');
    Route::get('/case-categories', [$controller, 'caseCategories']);
});

Route::middleware(['throttle:30,1', \App\Http\Middleware\AuthorizeContentWrite::class])->group(function () {
    Route::post('/team', [\App\Http\Controllers\TeamWriteController::class, 'store'])->name('api.team.store');
    Route::post('/team/{slug}', [\App\Http\Controllers\TeamWriteController::class, 'store'])
        ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')->name('api.team.replace');
    Route::post('/attorneys', [\App\Http\Controllers\AttorneyWriteController::class, 'store'])->name('api.attorneys.store');
    Route::post('/attorneys/{slug}', [\App\Http\Controllers\AttorneyWriteController::class, 'store'])
        ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')->name('api.attorneys.replace');
    Route::post('/cases', [\App\Http\Controllers\CaseStudyWriteController::class, 'store'])->name('api.cases.store');
    Route::post('/cases/{slug}', [\App\Http\Controllers\CaseStudyWriteController::class, 'store'])
        ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')->name('api.cases.replace');
    Route::post('/practice-areas', [\App\Http\Controllers\PracticeAreaWriteController::class, 'store'])->name('api.practice-areas.store');
    Route::post('/case-categories', [\App\Http\Controllers\CaseCategoryWriteController::class, 'store'])->name('api.case-categories.store');
});

Route::middleware(['throttle:30,1', 'auth:api'])->group(function () {
    Route::get('/me', [\App\Http\Controllers\AccountController::class, 'show'])
        ->middleware(['oauth.scope:account.view', 'can:account.view'])->name('api.account.show');
    Route::post('/users/{user}/role', [\App\Http\Controllers\AccountController::class, 'assignRole'])
        ->middleware(['oauth.scope:users.assign-roles', 'can:users.assign-roles'])->whereNumber('user')->name('api.users.role');
});

Route::middleware('throttle:5,1')->group(function () {
    Route::post('/auth/register', [\App\Http\Controllers\AuthController::class, 'register'])->name('api.auth.register');
});
Route::post('/auth/logout', [\App\Http\Controllers\AuthController::class, 'logout'])
    ->middleware(['throttle:30,1', 'auth:api'])->name('api.auth.logout');
