<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Root: redirect to the right portal based on which guard is authenticated
Route::get('/', function () {
    if (auth()->guard('web')->check())    return redirect()->route('admin.dashboard');
    if (auth()->guard('client')->check()) return redirect()->route('client.dashboard');
    return redirect()->route('login');
});

// Admin profile — web guard only
Route::middleware('auth:web')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
