<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\UnitController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified'])->group(function () {

    // Dashboard — any authenticated admin
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Projects
    Route::middleware('permission:view projects')->group(function () {
        Route::get('/projects',                        [ProjectController::class, 'index'])->name('projects.index');
        Route::middleware('permission:create projects')->group(function () {
            Route::get('/projects/create',             [ProjectController::class, 'create'])->name('projects.create');
            Route::post('/projects',                   [ProjectController::class, 'store'])->name('projects.store');
        });
        Route::middleware('permission:edit projects')->group(function () {
            Route::get('/projects/{project}/edit',     [ProjectController::class, 'edit'])->name('projects.edit');
            Route::put('/projects/{project}',          [ProjectController::class, 'update'])->name('projects.update');
        });
        Route::middleware('permission:delete projects')->group(function () {
            Route::delete('/projects/{project}',       [ProjectController::class, 'destroy'])->name('projects.destroy');
        });
    });

    // Units
    Route::middleware('permission:view units')->group(function () {
        Route::get('/units',                     [UnitController::class, 'index'])->name('units.index');
        Route::middleware('permission:create units')->group(function () {
            Route::get('/units/create',          [UnitController::class, 'create'])->name('units.create');
            Route::post('/units',                [UnitController::class, 'store'])->name('units.store');
        });
        Route::middleware('permission:edit units')->group(function () {
            Route::get('/units/{unit}/edit',     [UnitController::class, 'edit'])->name('units.edit');
            Route::put('/units/{unit}',          [UnitController::class, 'update'])->name('units.update');
        });
        Route::middleware('permission:delete units')->group(function () {
            Route::delete('/units/{unit}',       [UnitController::class, 'destroy'])->name('units.destroy');
        });
    });

    // Clients
    Route::middleware('permission:view clients')->group(function () {
        Route::get('/clients', fn () => inertia('Admin/Clients/Index'))->name('clients.index');
        Route::middleware('permission:create clients')->get('/clients/create', fn () => inertia('Admin/Clients/Create'))->name('clients.create');
        Route::middleware('permission:edit clients')->get('/clients/{client}/edit', fn () => inertia('Admin/Clients/Edit'))->name('clients.edit');
    });

    // Bookings
    Route::middleware('permission:view bookings')->group(function () {
        Route::get('/bookings', fn () => inertia('Admin/Bookings/Index'))->name('bookings.index');
        Route::middleware('permission:create bookings')->get('/bookings/create', fn () => inertia('Admin/Bookings/Create'))->name('bookings.create');
        Route::middleware('permission:edit bookings')->get('/bookings/{booking}/edit', fn () => inertia('Admin/Bookings/Edit'))->name('bookings.edit');
    });

    // Payments
    Route::middleware('permission:view payments')->group(function () {
        Route::get('/payments', fn () => inertia('Admin/Payments/Index'))->name('payments.index');
        Route::middleware('permission:create payments')->get('/payments/create', fn () => inertia('Admin/Payments/Create'))->name('payments.create');
    });

    // Construction
    Route::middleware('permission:view construction')->group(function () {
        Route::get('/construction', fn () => inertia('Admin/Construction/Index'))->name('construction.index');
        Route::middleware('permission:manage construction')->get('/construction/update', fn () => inertia('Admin/Construction/Update'))->name('construction.update');
    });

    // Documents
    Route::middleware('permission:view documents')->group(function () {
        Route::get('/documents', fn () => inertia('Admin/Documents/Index'))->name('documents.index');
    });

    // Reports
    Route::middleware('permission:view reports')->group(function () {
        Route::get('/reports', fn () => inertia('Admin/Reports/Index'))->name('reports.index');
    });

    // Settings — super_admin / company_admin only
    Route::middleware('permission:manage settings')->group(function () {
        Route::get('/settings', fn () => inertia('Admin/Settings/Index'))->name('settings.index');
    });

    // Media — admin manages all project/unit/client media
    // Clients READ media via model URLs directly (no route needed for viewing)
    // Client KYC upload handled in routes/client.php when client profile is built
    Route::middleware('permission:upload documents')->group(function () {
        Route::post('/media/{modelType}/{modelId}', [MediaController::class, 'upload'])->name('media.upload');
        Route::post('/media/reorder',               [MediaController::class, 'reorder'])->name('media.reorder');
    });
    Route::middleware('permission:delete documents')->group(function () {
        Route::delete('/media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');
    });
});
