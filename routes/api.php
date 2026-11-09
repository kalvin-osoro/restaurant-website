<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AttorneyWriteController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CaseCategoryWriteController;
use App\Http\Controllers\CaseStudyWriteController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\PracticeAreaWriteController;
use App\Http\Controllers\PublicContentController;
use App\Http\Controllers\TeamWriteController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => result('Service healthy', [
    'name' => config('app.name'),
    'status' => 'ok',
]))->name('api.health');

Route::middleware('throttle:5,1')->group(function () {
    Route::prefix('consultations')->group(function () {
        Route::post('/', [ConsultationController::class, 'store'])->name('api.consultations.store');
    });

    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->name('api.auth.register');
    });
});

Route::middleware('throttle:60,1')->group(function () {
    Route::prefix('team')->group(function () {
        Route::get('/', [PublicContentController::class, 'attorneys'])->name('api.team.index');
        Route::get('/{slug}', [PublicContentController::class, 'attorney'])
            ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')->name('api.team.show');
    });

    Route::prefix('attorneys')->group(function () {
        Route::get('/', [PublicContentController::class, 'attorneys']);
        Route::get('/{slug}', [PublicContentController::class, 'attorney'])
            ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*');
    });

    Route::prefix('practice-areas')->group(function () {
        Route::get('/', [PublicContentController::class, 'practiceAreas']);
    });

    Route::prefix('cases')->group(function () {
        Route::get('/', [PublicContentController::class, 'cases']);
        Route::get('/{slug}', [PublicContentController::class, 'caseStudy'])
            ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*');
    });

    Route::prefix('case-categories')->group(function () {
        Route::get('/', [PublicContentController::class, 'caseCategories']);
    });
});

// Content writes are temporarily public during CRUD development.
Route::middleware('throttle:30,1')->group(function () {
    Route::prefix('team')->group(function () {
        Route::post('/', [TeamWriteController::class, 'store'])->name('api.team.store');
        Route::post('/{slug}', [TeamWriteController::class, 'store'])
            ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')->name('api.team.replace');
    });

    Route::prefix('attorneys')->group(function () {
        Route::post('/', [AttorneyWriteController::class, 'store'])->name('api.attorneys.store');
        Route::post('/{slug}', [AttorneyWriteController::class, 'store'])
            ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')->name('api.attorneys.replace');
    });

    Route::prefix('cases')->group(function () {
        Route::post('/', [CaseStudyWriteController::class, 'store'])->name('api.cases.store');
        Route::post('/{slug}', [CaseStudyWriteController::class, 'store'])
            ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')->name('api.cases.replace');
    });

    Route::prefix('practice-areas')->group(function () {
        Route::post('/', [PracticeAreaWriteController::class, 'store'])->name('api.practice-areas.store');
    });

    Route::prefix('case-categories')->group(function () {
        Route::post('/', [CaseCategoryWriteController::class, 'store'])->name('api.case-categories.store');
    });
});

Route::middleware(['throttle:30,1', 'auth:api'])->group(function () {
    Route::prefix('me')->group(function () {
        Route::get('/', [AccountController::class, 'show'])
            ->middleware(['oauth.scope:account.view', 'can:account.view'])->name('api.account.show');
    });

    Route::prefix('users')->group(function () {
        Route::post('/{user}/role', [AccountController::class, 'assignRole'])
            ->middleware(['oauth.scope:users.assign-roles', 'can:users.assign-roles'])
            ->whereNumber('user')->name('api.users.role');
    });

    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
    });
});
