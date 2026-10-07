<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\ProgramApiController;
use App\Http\Controllers\Api\ProgramRecommendationController;
use App\Http\Controllers\Api\ProgramReviewController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/csrf-token', [AccountController::class, 'csrfToken'])
        ->middleware('web');

    Route::get('/programs', [ProgramApiController::class, 'index']);
    Route::get('/programs/{program}', [ProgramApiController::class, 'show']);

    Route::get(
        '/programs/{program}/recommend',
        [ProgramRecommendationController::class, 'show']
    )->middleware('throttle:60,1')->name('programs.recommend');

    // Tương thích ngược với endpoint cũ của Part VII.
    Route::get(
        '/programs/{program}/recommendations',
        [ProgramRecommendationController::class, 'show']
    )->middleware('throttle:60,1')->name('programs.recommendations');

    Route::middleware(['web', 'auth'])->group(function () {
        Route::get('/me', [AccountController::class, 'me']);

        Route::get(
            '/programs/{program}/review-eligibility',
            [ProgramReviewController::class, 'eligibility']
        );

        Route::post(
            '/programs/{program}/reviews',
            [ProgramReviewController::class, 'store']
        );
    });
});
