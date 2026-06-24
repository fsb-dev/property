<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_clients'     => 0,
                'active_projects'   => 0,
                'units_sold'        => 0,
                'revenue_collected' => 0,
            ],
            'recent_bookings' => [],
            'project_overview' => [],
        ]);
    }
}
