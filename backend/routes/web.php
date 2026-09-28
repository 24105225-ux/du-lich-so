<?php

use App\Http\Controllers\ClassRegistrationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProgramController;
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
    '/dang-ky-theo-lop',
    [ClassRegistrationController::class, 'create']
)->name('registrations.create');

Route::post(
    '/dang-ky-theo-lop',
    [ClassRegistrationController::class, 'store']
)->name('registrations.store');

Route::get(
    '/dang-ky-theo-lop/{registration}/thanh-cong',
    [ClassRegistrationController::class, 'success']
)->name('registrations.success');
Route::get(
    '/dang-ky-theo-lop/remaining-seats',
    [ClassRegistrationController::class, 'remainingSeats']
)->name('registrations.remaining-seats');
