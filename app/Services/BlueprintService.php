<?php

namespace App\Services;

use App\Enums\UnitStatus;
use App\Models\Project;
use App\Models\ProjectBlock;
use App\Models\Unit;

class BlueprintService
{
    // ── Page data ──────────────────────────────────────────────────────────

    public function forBlueprint(Project $project): array
    {
        $buildings = $project->buildings()
            ->with(['sections' => fn($q) => $q->with(['units' => fn($q) => $q->orderBy('floor')->orderBy('sort_order')])])
            ->orderBy('sort_order')
            ->get();

        return $buildings->map(fn($building) => [
            'id'           => $building->id,
            'name'         => $building->name,
            'total_floors' => $building->total_floors,
            'sections'     => $building->sections->map(fn($section) => $this->formatSection($section)),
        ])->toArray();
    }

    public function formatSection(ProjectBlock $section): array
    {
        $base = [
            'id'             => $section->id,
            'name'           => $section->name,
            'type'           => $section->type,
            'floor_start'    => $section->floor_start,
            'floor_end'      => $section->floor_end,
            'planned_units'  => $section->planned_units,
            'is_block_unit'  => (bool) $section->is_block_unit,
        ];

        if ($section->is_block_unit) {
            // Block unit section — one unit covers all floors
            $unit = $section->units->first();
            return array_merge($base, [
                'block_unit' => $unit ? $this->formatUnit($unit) : null,
                'floors'     => [],
            ]);
        }

        // Normal per-floor section
        $unitsByFloor = $section->units->groupBy('floor');
        $floors = [];
        for ($f = $section->floor_start; $f <= $section->floor_end; $f++) {
            $units = $unitsByFloor->get($f, collect());
            $floors[] = [
                'floor'      => $f,
                'unit_count' => $units->count(),
                'units'      => $units->map(fn($u) => $this->formatUnit($u))->values()->toArray(),
            ];
        }

        return array_merge($base, ['block_unit' => null, 'floors' => $floors]);
    }

    public function formatUnit(Unit $unit): array
    {
        return [
            'id'           => $unit->id,
            'unit_number'  => $unit->unit_number,
            'unit_code'    => $unit->unit_code,
            'floor'        => $unit->floor,
            'floor_end'    => $unit->floor_end,
            'sort_order'   => $unit->sort_order,
            'status'       => $unit->status->value,
            'status_label' => $unit->status->label(),
            'status_color' => $unit->status->color(),
            'type'         => $unit->type?->value,
            'type_label'   => $unit->type?->label(),
            'wing'         => $unit->wing,
            'bedrooms'     => $unit->bedrooms,
            'bathrooms'    => $unit->bathrooms,
            'balconies'    => $unit->balconies,
            'size_sqft'    => $unit->size_sqft,
            'price'        => $unit->price ? (float) $unit->price : null,
        ];
    }

    // ── Block unit generation ──────────────────────────────────────────────

    /**
     * Create a single unit that spans the entire section floor range.
     * Idempotent — skips if a unit already exists for this section.
     */
    public function generateBlockUnit(ProjectBlock $section): array
    {
        $existing = Unit::where('block_id', $section->id)->first();

        if ($existing) {
            return ['skipped' => true, 'unit' => $this->formatUnit($existing)];
        }

        $prefix = $this->resolvePrefix($section);
        $label  = sprintf('%s-%d-%d', $prefix, $section->floor_start, $section->floor_end);

        $unit = Unit::create([
            'project_id'  => $section->project_id,
            'block_id'    => $section->id,
            'floor'       => $section->floor_start,
            'floor_end'   => $section->floor_end,
            'sort_order'  => 1,
            'unit_number' => $label,
            'type'        => $this->defaultTypeForSection($section),
            'status'      => UnitStatus::NotConfigured,
        ]);

        return ['skipped' => false, 'unit' => $this->formatUnit($unit)];
    }

    // ── Generate ───────────────────────────────────────────────────────────

    public function generateFloor(ProjectBlock $section, int $floor, int $count): array
    {
        $existing = Unit::where('block_id', $section->id)->where('floor', $floor)->count();
        $totalExisting = Unit::where('block_id', $section->id)->count();



        if ($existing > 0) {
            return ['skipped' => true, 'message' => "Floor {$floor} already has {$existing} units."];
        }

        if ($totalExisting + $count > $section->planned_units) {
            return ['skipped' => true, 'message' => "Unit capacity of {$section->planned_units} has been exceeded."];
        }

        $prefix = $this->resolvePrefix($section);
        $units  = [];

        for ($i = 1; $i <= $count; $i++) {
            $units[] = Unit::create([
                'project_id'  => $section->project_id,
                'block_id'    => $section->id,
                'floor'       => $floor,
                'sort_order'  => $i,
                'unit_number' => $this->buildUnitNumber($prefix, $floor, $i),
                'type'        => $this->defaultTypeForSection($section),
                'status'      => UnitStatus::NotConfigured,
            ]);
        }

        return [
            'skipped' => false,
            'floor'   => $floor,
            'units'   => collect($units)->map(fn($u) => $this->formatUnit($u))->toArray(),
        ];
    }

    public function generateAllFloors(ProjectBlock $section, int $unitsPerFloor): array
    {
        $results = [];
        for ($f = $section->floor_start; $f <= $section->floor_end; $f++) {
            $results[$f] = $this->generateFloor($section, $f, $unitsPerFloor);
        }
        return $results;
    }

    // ── Unit config ────────────────────────────────────────────────────────

    public function saveUnit(Unit $unit, array $data): Unit
    {
        $wasNotConfigured = $unit->status === UnitStatus::NotConfigured;

        $unit->update($data);

        if ($wasNotConfigured && $unit->fresh()->status === UnitStatus::NotConfigured) {
            $unit->update(['status' => UnitStatus::Configured]);
        }

        return $unit->fresh();
    }

    /**
     * Apply a raw config payload to all units of the SAME TYPE on the same floor.
     * Only touches not_configured units unless overwrite=true.
     * Returns count of updated units.
     */
    public function applyQuickConfig(ProjectBlock $section, int $floor, string $type, array $config, bool $overwrite = false): int
    {
        $query = Unit::where('block_id', $section->id)
            ->where('floor', $floor)
            ->where('type', $type);

        if (!$overwrite) {
            $query->where('status', UnitStatus::NotConfigured);
        }

        $targets = $query->get();

        foreach ($targets as $unit) {
            $unit->update(array_merge($config, ['status' => UnitStatus::Configured]));
        }

        return $targets->count();
    }

    /**
     * Copy one unit's config to all same-TYPE units on the same floor.
     * Filters by type so apartment config never bleeds into commercial units.
     */
    public function applyConfigToFloor(Unit $sourceUnit, bool $overwriteConfigured = false): int
    {
        $config = $this->extractConfig($sourceUnit);
        $sourceType = $sourceUnit->getRawOriginal('type');

        $query = Unit::where('block_id', $sourceUnit->block_id)
            ->where('floor', $sourceUnit->floor)
            ->where('id', '!=', $sourceUnit->id)
            ->where('type', $sourceType); // only same-type units

        if (!$overwriteConfigured) {
            $query->where('status', UnitStatus::NotConfigured);
        }

        $targets = $query->get();

        foreach ($targets as $unit) {
            $unit->update(array_merge($config, ['status' => UnitStatus::Configured]));
        }

        return $targets->count();
    }

    // ── Delete ─────────────────────────────────────────────────────────────

    public function deleteFloorUnits(ProjectBlock $section, int $floor): int
    {
        return Unit::where('block_id', $section->id)
            ->where('floor', $floor)
            ->whereIn('status', [UnitStatus::NotConfigured, UnitStatus::Configured])
            ->delete();
    }

    // ── Bulk ───────────────────────────────────────────────────────────────

    public function bulkUpdateStatus(array $unitIds, UnitStatus $status): int
    {
        return Unit::whereIn('id', $unitIds)->update(['status' => $status]);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function resolvePrefix(ProjectBlock|null $section): string
    {
        if (!$section) return 'U';
        $words  = preg_split('/[\s_]+/', trim($section->name ?? ''));
        $prefix = collect($words)
            ->filter(fn($w) => strlen($w) > 0)
            ->map(fn($w) => strtoupper($w[0]))
            ->take(2)
            ->implode('');
        return $prefix ?: 'U';
    }

    private function buildUnitNumber(string $prefix, int $floor, int $index): string
    {
        return sprintf('%s-%d%02d', $prefix, $floor, $index);
    }

    private function defaultTypeForSection(ProjectBlock $section): ?string
    {
        return match ($section->type) {
            'residential' => 'apartment',
            'villa'       => 'villa',
            'commercial'  => 'shop',
            'office'      => 'office',
            'industrial'  => 'warehouse',
            default       => null, // mixed — user picks type manually
        };
    }

    private function extractConfig(Unit $unit): array
    {
        return array_filter([
            'type'          => $unit->getRawOriginal('type'),
            'wing'          => $unit->wing,
            'bedrooms'      => $unit->bedrooms,
            'bathrooms'     => $unit->bathrooms,
            'balconies'     => $unit->balconies,
            'servant_room'  => $unit->servant_room,
            'store_room'    => $unit->store_room,
            'size_sqft'     => $unit->size_sqft,
            'view'          => $unit->view,
            'price'         => $unit->price,
            'facing_direction' => $unit->facing_direction,
        ], fn($v) => !is_null($v));
    }
}
