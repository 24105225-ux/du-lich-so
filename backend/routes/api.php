<?php

use App\Http\Controllers\Api\ProgramApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get(
        '/programs',
        [ProgramApiController::class, 'index']
    );

    Route::get(
        '/programs/{program}',
        [ProgramApiController::class, 'show']
    );
});
