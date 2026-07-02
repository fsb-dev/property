<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSiteUpdateRequest;
use App\Services\ConstructionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ConstructionController extends Controller
{
    public function __construct(private readonly ConstructionService $service) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Admin/Construction/Index', [
            'kpis'             => $this->service->kpis(),
            'progressOverview' => $this->service->progressOverview(),
            'progressTrend'    => $this->service->progressTrend(),
            'progressByProject' => $this->service->progressByProject(),
            'upcomingMilestones' => $this->service->upcomingMilestones(),
            'siteActivity'     => $this->service->siteActivity(),
            'table'            => $this->service->table($request),
            'projectOptions'   => $this->service->projectOptions(),
            'filters'          => $request->only('search'),
        ]);
    }

    public function storeSiteUpdate(StoreSiteUpdateRequest $request): RedirectResponse
    {
        $this->service->createSiteUpdate($request->validated());

        return redirect()->route('admin.construction.index')
            ->with('toast', ['type' => 'success', 'message' => 'Site update added.']);
    }
}
