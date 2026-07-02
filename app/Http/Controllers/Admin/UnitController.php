<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUnitRequest;
use App\Http\Requests\Admin\UpdateUnitRequest;
use App\Models\Unit;
use App\Services\UnitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class UnitController extends Controller
{
    public function __construct(private readonly UnitService $service) {}

    public function index(Request $request): Response
    {
        return inertia('Admin/Units/Index', [
            'units'    => $this->service->paginate($request),
            'filters'  => $request->only(['search', 'project_id', 'block_id', 'type', 'status']),
            'stats'    => $this->service->stats(),
            'enums'    => $this->service->enums(),
            'projects' => $this->service->projectsList(),
            'sections' => $this->service->sectionsList(),
        ]);
    }

    public function create(): Response
    {
        return inertia('Admin/Units/Create', [
            'enums'    => $this->service->enums(),
            'projects' => $this->service->projectsList(),
        ]);
    }

    public function search(Request $request)
    {
        if ($id = $request->integer('id')) {
            $unit = Unit::find($id);

            return response()->json($unit ? $this->service->brief($unit) : null);
        }

        return response()->json(
            $this->service->search($request->only(['project_id', 'building_id', 'q']))
        );
    }

    public function store(StoreUnitRequest $request): RedirectResponse
    {
        $unit = $this->service->create($request->validated());

        return redirect()->route('admin.units.index')
            ->with('toast', ['type' => 'success', 'message' => "Unit \"{$unit->unit_number}\" created."]);
    }

    public function edit(Unit $unit): Response
    {
        return inertia('Admin/Units/Edit', [
            'unit'     => $this->service->forConfigure($unit),
            'enums'    => $this->service->enums(),
            'projects' => $this->service->projectsList(),
        ]);
    }

    /** Full 8-tab configuration page — entry point from blueprint circle click */
    public function configure(Unit $unit): Response
    {
        return inertia('Admin/Units/Configure', [
            'unit'  => $this->service->forConfigure($unit),
            'enums' => $this->service->enums(),
        ]);
    }

    public function update(UpdateUnitRequest $request, Unit $unit): RedirectResponse
    {
        $unit = $this->service->update($unit, $request->validated());

        // Return to blueprint if that's where the user came from
        $from = $request->input('_from');
        if ($from === 'blueprint' && $unit->block_id) {
            $projectId = $unit->project_id;
            return redirect()->route('admin.blueprint.show', $projectId)
                ->with('toast', ['type' => 'success', 'message' => "Unit \"{$unit->unit_number}\" saved."]);
        }

        return redirect()->route('admin.units.index')
            ->with('toast', ['type' => 'success', 'message' => "Unit \"{$unit->unit_number}\" updated."]);
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        $label = $this->service->delete($unit);

        return redirect()->route('admin.units.index')
            ->with('toast', ['type' => 'success', 'message' => "{$label} deleted."]);
    }
}
