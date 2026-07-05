<?php

use App\Http\Controllers\Client\Auth\SessionController;
use App\Http\Controllers\Client\DashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('client')->name('client.')->group(function () {

    // Guest-only routes (redirect to dashboard if already logged in)
    Route::middleware('guest.client')->group(function () {
        Route::get('/login',  [SessionController::class, 'create'])->name('login');
        Route::post('/login', [SessionController::class, 'store'])->name('login.store');
    });

    // Authenticated client routes
    Route::middleware('auth.client')->group(function () {
        Route::post('/logout',    [SessionController::class, 'destroy'])->name('logout');
        Route::get('/dashboard',  [DashboardController::class, 'index'])->name('dashboard');
    });

});
