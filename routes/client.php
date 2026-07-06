<?php

use App\Http\Controllers\Client\AiAdvisorController;
use App\Http\Controllers\Client\Auth\ProfileController;
use App\Http\Controllers\Client\Auth\SessionController;
use App\Http\Controllers\Client\CommunityFutureController;
use App\Http\Controllers\Client\ConstructionProgressController;
use App\Http\Controllers\Client\DashboardController;
use App\Http\Controllers\Client\DocumentsController;
use App\Http\Controllers\Client\InvestmentAnalyzerController;
use App\Http\Controllers\Client\MortgageController;
use App\Http\Controllers\Client\PaymentsController;
use App\Http\Controllers\Client\PropertiesController;
use App\Http\Controllers\Client\SupportController;
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
        Route::get('/profile',    [ProfileController::class, 'show'])->name('profile.show');
        Route::put('/profile',    [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/password',   [ProfileController::class, 'updatePassword'])->name('password.update');
        Route::get('/dashboard',    [DashboardController::class,    'index'])->name('dashboard');
        Route::get('/properties',         [PropertiesController::class, 'index'])->name('properties');
        Route::get('/payments',           [PaymentsController::class,   'index'])->name('payments');
        Route::get('/mortgage',           [MortgageController::class,   'index'])->name('mortgage');
        Route::get('/mortgage/explore',   [MortgageController::class,   'explore'])->name('mortgage.explore');
        Route::get('/mortgage/{booking}', [MortgageController::class,   'show'])->name('mortgage.show');
        Route::get('/investment-analyzer',   [InvestmentAnalyzerController::class,    'index'])->name('investment');
        Route::get('/construction-progress', [ConstructionProgressController::class, 'index'])->name('construction');
        Route::get('/community-future',  [CommunityFutureController::class, 'index'])->name('community.future');
        Route::get('/ai-advisor',        [AiAdvisorController::class,      'index'])->name('ai.advisor');
        Route::get('/documents',         [DocumentsController::class,      'index'])->name('documents');
        Route::get('/support',           [SupportController::class,        'index'])->name('support');
    });
});
