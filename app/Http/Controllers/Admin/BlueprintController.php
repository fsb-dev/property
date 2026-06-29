<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UnitStatus;
use App\Enums\UnitType;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectBlock;
use App\Models\Unit;
use App\Services\BlueprintService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class BlueprintController extends Controller
{
    public function __construct(private readonly BlueprintService $service) {}

    // ── Project selector (Units sidebar entry point) ───────────────────────

    public function select(): Response
    {
        $projects = Project::withCount(['buildings', 'units'])
            ->orderBy('name')
            ->get()
            ->map(fn($p) => [
                'id'              => $p->id,
                'name'            => $p->name,
                'location'        => $p->location,
                'status'          => $p->getRawOriginal('status'),
                'status_label'    => $p->status?->label() ?? $p->getRawOriginal('status'),
                'buildings_count' => $p->buildings_count,
                'units_count'     => $p->units_count,
            ]);

        return inertia('Admin/Units/BlueprintSelect', ['projects' => $projects]);
    }

    // ── Blueprint page ─────────────────────────────────────────────────────

    public function show(Project $project): Response
    {
        return inertia('Admin/Units/Blueprint', [
            'project'   => [
                'id'   => $project->id,
                'name' => $project->name,
                'type' => $project->type?->value ?? $project->getRawOriginal('type'),
            ],
            'buildings' => $this->service->forBlueprint($project),
            'enums'     => $this->enums(),
        ]);
    }

    // ── Generate units on a floor ──────────────────────────────────────────

    public function generate(Request $request, ProjectBlock $section): JsonResponse
    {
        $data = $request->validate([
            'floor' => ['required', 'integer', 'min:1', 'max:300'],
            'count' => ['required', 'integer', 'min:1', 'max:200'],
        ]);

        $result = $this->service->generateFloor($section, $data['floor'], $data['count']);

        return response()->json($result, $result['skipped'] ? 409 : 201);
    }

    // ── Generate all floors in a section ──────────────────────────────────

    public function generateAll(Request $request, ProjectBlock $section): JsonResponse
    {
        $data = $request->validate([
            'units_per_floor' => ['required', 'integer', 'min:1', 'max:200'],
        ]);

        $results = $this->service->generateAllFloors($section, $data['units_per_floor']);

        return response()->json(['floors' => $results], 201);
    }

    // ── Quick-apply a config to all same-type units on a floor ────────────

    public function quickConfig(Request $request, ProjectBlock $section): JsonResponse
    {
        $data = $request->validate([
            'floor'            => ['required', 'integer'],
            'type'             => ['required', 'string'],
            'overwrite'        => ['boolean'],
            'wing'             => ['nullable', 'string', 'max:30'],
            'bedrooms'         => ['nullable', 'integer', 'min:0', 'max:20'],
            'bathrooms'        => ['nullable', 'integer', 'min:0', 'max:20'],
            'balconies'        => ['nullable', 'integer', 'min:0', 'max:10'],
            'servant_room'     => ['nullable', 'boolean'],
            'store_room'       => ['nullable', 'boolean'],
            'size_sqft'        => ['nullable', 'integer', 'min:1'],
            'facing_direction' => ['nullable', 'string', 'max:30'],
            'view'             => ['nullable', 'string', 'max:100'],
            'price'            => ['nullable', 'numeric', 'min:0'],
        ]);

        $config = array_filter(
            collect($data)->except(['floor', 'type', 'overwrite'])->toArray(),
            fn($v) => !is_null($v),
        );

        $updated = $this->service->applyQuickConfig(
            $section,
            $data['floor'],
            $data['type'],
            $config,
            $data['overwrite'] ?? false,
        );

        return response()->json(['updated' => $updated]);
    }

    // ── Save individual unit config (lightweight blueprint update) ─────────

    public function updateUnit(Request $request, Unit $unit): JsonResponse
    {
        $data = $request->validate([
            'type'             => ['nullable', 'string'],
            'wing'             => ['nullable', 'string', 'max:30'],
            'bedrooms'         => ['nullable', 'integer', 'min:0', 'max:20'],
            'bathrooms'        => ['nullable', 'integer', 'min:0', 'max:20'],
            'balconies'        => ['nullable', 'integer', 'min:0', 'max:10'],
            'servant_room'     => ['nullable', 'boolean'],
            'store_room'       => ['nullable', 'boolean'],
            'size_sqft'        => ['nullable', 'integer', 'min:1'],
            'facing_direction' => ['nullable', 'string', 'max:30'],
            'view'             => ['nullable', 'string', 'max:100'],
            'price'            => ['nullable', 'numeric', 'min:0'],
            'status'           => ['nullable', 'string'],
            'unit_number'      => ['nullable', 'string', 'max:20'],
        ]);

        $unit = $this->service->saveUnit($unit, array_filter($data, fn($v) => !is_null($v)));

        return response()->json($this->service->formatUnit($unit));
    }

    // ── Copy one unit's config to same-type units on its floor ────────────

    public function applyConfig(Request $request, Unit $unit): JsonResponse
    {
        $data = $request->validate([
            'overwrite' => ['boolean'],
        ]);

        $updated = $this->service->applyConfigToFloor($unit, $data['overwrite'] ?? false);

        return response()->json(['updated' => $updated]);
    }

    // ── Delete all units on a floor (only not_configured + configured) ─────

    public function deleteFloor(Request $request, ProjectBlock $section): JsonResponse
    {
        $data = $request->validate([
            'floor' => ['required', 'integer'],
        ]);

        $deleted = $this->service->deleteFloorUnits($section, $data['floor']);

        return response()->json(['deleted' => $deleted]);
    }

    // ── Bulk status update ─────────────────────────────────────────────────

    public function bulkStatus(Request $request): JsonResponse
    {
        $data = $request->validate([
            'unit_ids'   => ['required', 'array', 'min:1'],
            'unit_ids.*' => ['integer', 'exists:units,id'],
            'status'     => ['required', 'string'],
        ]);

        $status  = UnitStatus::from($data['status']);
        $updated = $this->service->bulkUpdateStatus($data['unit_ids'], $status);

        return response()->json(['updated' => $updated]);
    }

    // ── Shared enum data ───────────────────────────────────────────────────

    private function enums(): array
    {
        return [
            'unit_types' => collect(UnitType::cases())->map(fn($e) => [
                'value' => $e->value,
                'label' => $e->label(),
            ]),
            'unit_statuses' => collect(UnitStatus::cases())->map(fn($e) => [
                'value' => $e->value,
                'label' => $e->label(),
                'color' => $e->color(),
            ]),
            'views'           => ['Sea View', 'Lake View', 'Garden View', 'City View', 'Pool View', 'Street View', 'Open View'],
            'facing'          => ['North', 'South', 'East', 'West', 'North-East', 'North-West', 'South-East', 'South-West'],
        ];
    }
}
