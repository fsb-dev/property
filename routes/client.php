<?php

use App\Http\Controllers\Client\Auth\SessionController;
use App\Http\Controllers\Client\ConstructionProgressController;
use App\Http\Controllers\Client\DashboardController;
use App\Http\Controllers\Client\InvestmentAnalyzerController;
use App\Http\Controllers\Client\MortgageController;
use App\Http\Controllers\Client\PaymentsController;
use App\Http\Controllers\Client\PropertiesController;
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
        Route::get('/dashboard',    [DashboardController::class,    'index'])->name('dashboard');
        Route::get('/properties',         [PropertiesController::class, 'index'])->name('properties');
        Route::get('/payments',           [PaymentsController::class,   'index'])->name('payments');
        Route::get('/mortgage',           [MortgageController::class,   'index'])->name('mortgage');
        Route::get('/mortgage/explore',   [MortgageController::class,   'explore'])->name('mortgage.explore');
        Route::get('/mortgage/{booking}', [MortgageController::class,   'show'])->name('mortgage.show');
        Route::get('/investment-analyzer',   [InvestmentAnalyzerController::class,    'index'])->name('investment');
        Route::get('/construction-progress', [ConstructionProgressController::class, 'index'])->name('construction');
    });

});
