<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreConstructionStatusRequest;
use App\Http\Requests\Admin\StoreSiteUpdateRequest;
use App\Models\Project;
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
            'statusOptions'    => $this->service->statusOptions(),
            'allProjects'      => $this->service->allProjects(),
            'filters'          => $request->only('search'),
        ]);
    }

    public function storeSiteUpdate(StoreSiteUpdateRequest $request): RedirectResponse
    {
        $this->service->createSiteUpdate($request->validated());

        return redirect()->route('admin.construction.index')
            ->with('toast', ['type' => 'success', 'message' => 'Site update added.']);
    }

    public function storeStatus(StoreConstructionStatusRequest $request): RedirectResponse
    {
        $this->service->upsertConstructionStatus($request->validated());

        return redirect()->route('admin.construction.index')
            ->with('toast', ['type' => 'success', 'message' => 'Construction status saved.']);
    }

    public function destroyStatus(Project $project): RedirectResponse
    {
        $this->service->deleteConstructionStatus($project->id);

        return redirect()->route('admin.construction.index')
            ->with('toast', ['type' => 'success', 'message' => 'Construction status reset.']);
    }
}
