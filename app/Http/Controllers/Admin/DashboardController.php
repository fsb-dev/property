<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use App\Services\DashboardService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $service,
        private readonly ActivityLogger $activityLogger,
    ) {}

    public function index(): Response
    {
        $tickets = json_decode(file_get_contents(resource_path('data/support-tickets.json')), true);

        return Inertia::render('Admin/Dashboard', [
            'stats'           => $this->service->stats(),
            'recentBookings'  => $this->service->recentBookings(),
            'projectProgress' => $this->service->projectProgress(),
            'salesInventory'  => $this->service->salesInventory(),
            'paymentTrend'    => $this->service->paymentTrend(),
            'topPerforming'   => $this->service->topPerforming(),
            'supportOverview' => $tickets['overview'] ?? null,
            'recentActivity'  => $this->activityLogger->recent(6),
            'quickActions'    => [
                ['label' => 'Add New Project', 'icon' => 'project', 'color' => 'accent', 'href' => route('admin.projects.create')],
                ['label' => 'Add New Unit',    'icon' => 'unit',    'color' => 'blue',   'href' => route('admin.units.create')],
                ['label' => 'Add New Buyer',   'icon' => 'buyer',   'color' => 'green',  'href' => route('admin.clients.create')],
                ['label' => 'New Reservation', 'icon' => 'payment', 'color' => 'orange', 'href' => route('admin.bookings.create')],
                ['label' => 'Support Tickets', 'icon' => 'document','color' => 'red',    'href' => route('admin.support-tickets.index')],
                ['label' => 'View Reports',    'icon' => 'notify',  'color' => 'sky',    'href' => route('admin.analysis.index')],
            ],
        ]);
    }
}
