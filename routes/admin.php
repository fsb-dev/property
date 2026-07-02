<?php

use App\Http\Controllers\Admin\BlueprintController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InvestmentController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\PaymentController;
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

    // Units — CRUD
    Route::middleware('permission:view units')->group(function () {
        Route::get('/units',                     [UnitController::class, 'index'])->name('units.index');
        Route::get('/units/search',              [UnitController::class, 'search'])->name('units.search');
        Route::middleware('permission:create units')->group(function () {
            Route::get('/units/create',          [UnitController::class, 'create'])->name('units.create');
            Route::post('/units',                [UnitController::class, 'store'])->name('units.store');
        });
        Route::middleware('permission:edit units')->group(function () {
            Route::get('/units/{unit}/edit',      [UnitController::class, 'edit'])->name('units.edit');
            Route::get('/units/{unit}/configure', [UnitController::class, 'configure'])->name('units.configure');
            Route::put('/units/{unit}',           [UnitController::class, 'update'])->name('units.update');
        });
        Route::middleware('permission:delete units')->group(function () {
            Route::delete('/units/{unit}',       [UnitController::class, 'destroy'])->name('units.destroy');
        });
    });

    // Blueprint — visual unit builder (JSON API + Inertia pages)
    Route::middleware('permission:edit units')->group(function () {
        Route::get( '/units/blueprint',                           [BlueprintController::class, 'select'])->name('units.blueprint-select');
        Route::get( '/projects/{project}/blueprint',              [BlueprintController::class, 'show'])->name('blueprint.show');
        Route::post('/sections/{section}/generate',               [BlueprintController::class, 'generate'])->name('blueprint.generate');
        Route::post('/sections/{section}/generate-all',           [BlueprintController::class, 'generateAll'])->name('blueprint.generate-all');
        Route::post('/sections/{section}/generate-block',         [BlueprintController::class, 'generateBlock'])->name('blueprint.generate-block');
        Route::post('/sections/{section}/quick-config',           [BlueprintController::class, 'quickConfig'])->name('blueprint.quick-config');
        Route::patch('/units/{unit}/blueprint',                   [BlueprintController::class, 'updateUnit'])->name('blueprint.unit.update');
        Route::post( '/units/{unit}/apply-config',                [BlueprintController::class, 'applyConfig'])->name('blueprint.unit.apply');
        Route::delete('/sections/{section}/floor',                [BlueprintController::class, 'deleteFloor'])->name('blueprint.floor.delete');
        Route::post( '/units/bulk-status',                        [BlueprintController::class, 'bulkStatus'])->name('blueprint.bulk-status');
    });

    // Payments
    Route::get('/payments',         [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/records', [PaymentController::class, 'records'])->name('payments.records');
    Route::get('/payments/record',  [PaymentController::class, 'record'])->name('payments.record');

    // Clients — static segments (/create) must come before wildcard ({client})
    Route::middleware('permission:view clients')->group(function () {
        Route::get('/clients',                        [ClientController::class, 'index'])->name('clients.index');
        Route::get('/clients/search',                 [ClientController::class, 'search'])->name('clients.search');
        Route::middleware('permission:create clients')->group(function () {
            Route::get('/clients/create',             [ClientController::class, 'create'])->name('clients.create');
            Route::post('/clients',                   [ClientController::class, 'store'])->name('clients.store');
        });
        Route::get('/clients/{client}',               [ClientController::class, 'show'])->name('clients.show');
        Route::middleware('permission:edit clients')->group(function () {
            Route::get('/clients/{client}/edit',      [ClientController::class, 'edit'])->name('clients.edit');
            Route::match(['put', 'post'], '/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
        });
        Route::middleware('permission:delete clients')->group(function () {
            Route::delete('/clients/{client}',        [ClientController::class, 'destroy'])->name('clients.destroy');
        });
    });

    // Bookings
    Route::middleware('permission:view bookings')->group(function () {
        Route::get('/bookings',              [BookingController::class, 'index'])->name('bookings.index');
        Route::middleware('permission:create bookings')->group(function () {
            Route::get('/bookings/create',   [BookingController::class, 'create'])->name('bookings.create');
            Route::post('/bookings',         [BookingController::class, 'store'])->name('bookings.store');
            Route::post('/bookings/draft',   [BookingController::class, 'saveDraft'])->name('bookings.draft');
        });
        Route::get('/bookings/{booking}',    [BookingController::class, 'show'])->name('bookings.show');
        Route::middleware('permission:edit bookings')->group(function () {
            Route::get('/bookings/{booking}/edit',      [BookingController::class, 'edit'])->name('bookings.edit');
            Route::put('/bookings/{booking}',           [BookingController::class, 'update'])->name('bookings.update');
            Route::patch('/bookings/{booking}/status',  [BookingController::class, 'updateStatus'])->name('bookings.status');
        });
        Route::middleware('permission:delete bookings')->group(function () {
            Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');
        });
    });

    // Investment Data — analytics-only page, sourced from a static JSON fixture (no DB)
    Route::get('/investment', [InvestmentController::class, 'index'])->name('investment.index');

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
