<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClassRegistrationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\RoleDashboardController;
use App\Http\Controllers\ThemeController;
use Illuminate\Support\Facades\Route;

Route::get(
    '/',
    [HomeController::class, 'index']
)->name('home');

Route::get(
    '/chuong-trinh',
    [ProgramController::class, 'index']
)->name('programs.index');

Route::get(
    '/chuong-trinh/{program}',
    [ProgramController::class, 'show']
)->name('programs.show');

Route::get(
    '/dang-nhap',
    [AuthController::class, 'create']
)->name('login');

Route::post(
    '/dang-nhap',
    [AuthController::class, 'store']
)
    ->middleware('throttle:5,1')
    ->name('login.store');

Route::post(
    '/dang-xuat',
    [AuthController::class, 'destroy']
)
    ->middleware('auth')
    ->name('logout');

Route::post(
    '/theme',
    [ThemeController::class, 'switch']
)->name('theme.switch');

Route::middleware('auth')->group(function () {
    Route::get(
        '/dashboard',
        [RoleDashboardController::class, 'index']
    )->name('dashboard');

    Route::get(
        '/doi-mat-khau',
        [PasswordController::class, 'edit']
    )->name('password.edit');

    Route::post(
        '/doi-mat-khau',
        [PasswordController::class, 'update']
    )->name('password.update');

    Route::middleware(
        'role:admin,school'
    )
        ->prefix('nha-truong')
        ->group(function () {
            Route::get(
                '/dashboard',
                [RoleDashboardController::class, 'index']
            )->name('school.dashboard');

            Route::get(
                '/dang-ky-theo-lop/remaining-seats',
                [ClassRegistrationController::class, 'remainingSeats']
            )->name('registrations.remaining-seats');
            Route::get(
                '/dang-ky-theo-lop',
                [ClassRegistrationController::class, 'create']
            )->name('registrations.create');

            Route::post(
                '/dang-ky-theo-lop',
                [ClassRegistrationController::class, 'store']
            )->name('registrations.store');

            Route::get(
                '/dang-ky-theo-lop/ket-qua/{token}',
                [ClassRegistrationController::class, 'success']
            )
                ->where(
                    'token',
                    '.+'
                )
                ->name('registrations.success');
        });

    Route::middleware(
        'role:admin,parent'
    )
        ->prefix('phu-huynh')
        ->group(function () {
            Route::get(
                '/dashboard',
                [RoleDashboardController::class, 'index']
            )->name('parent.dashboard');
        });

    Route::middleware(
        'role:admin,organizer'
    )
        ->prefix('don-vi-to-chuc')
        ->group(function () {
            Route::get(
                '/dashboard',
                [RoleDashboardController::class, 'index']
            )->name('organizer.dashboard');
        });
});
