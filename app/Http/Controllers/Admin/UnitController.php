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
            'filters'  => $request->only(['search', 'project_id', 'type', 'status']),
            'stats'    => $this->service->stats(),
            'enums'    => $this->service->enums(),
            'projects' => $this->service->projectsList(),
        ]);
    }

    public function create(): Response
    {
        return inertia('Admin/Units/Create', [
            'enums'    => $this->service->enums(),
            'projects' => $this->service->projectsList(),
        ]);
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
            'unit'     => $this->service->forEdit($unit),
            'enums'    => $this->service->enums(),
            'projects' => $this->service->projectsList(),
        ]);
    }

    public function update(UpdateUnitRequest $request, Unit $unit): RedirectResponse
    {
        $unit = $this->service->update($unit, $request->validated());

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
