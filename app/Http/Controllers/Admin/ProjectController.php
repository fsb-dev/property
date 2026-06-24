<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProjectRequest;
use App\Http\Requests\Admin\UpdateProjectRequest;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class ProjectController extends Controller
{
    public function __construct(private readonly ProjectService $service) {}

    public function index(\Illuminate\Http\Request $request): Response
    {
        return inertia('Admin/Projects/Index', [
            'projects' => $this->service->paginate($request),
            'filters'  => $request->only(['search', 'type', 'status']),
            'stats'    => $this->service->stats(),
            'enums'    => $this->service->enums(),
        ]);
    }

    public function create(): Response
    {
        return inertia('Admin/Projects/Create', [
            'enums' => $this->service->enums(),
        ]);
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $project = $this->service->create($request->validated());

        return redirect()->route('admin.projects.index')
            ->with('toast', ['type' => 'success', 'message' => "Project \"{$project->name}\" created."]);
    }

    public function edit(Project $project): Response
    {
        return inertia('Admin/Projects/Edit', [
            'project' => $this->service->forEdit($project),
            'enums'   => $this->service->enums(),
        ]);
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $project = $this->service->update($project, $request->validated());

        return redirect()->route('admin.projects.index')
            ->with('toast', ['type' => 'success', 'message' => "Project \"{$project->name}\" updated."]);
    }

    public function destroy(Project $project): RedirectResponse
    {
        $name = $this->service->delete($project);

        return redirect()->route('admin.projects.index')
            ->with('toast', ['type' => 'success', 'message' => "Project \"{$name}\" deleted."]);
    }
}
