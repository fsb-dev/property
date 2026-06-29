<?php

namespace App\Services;

use App\Enums\ProjectStatus;
use App\Enums\ProjectType;
use App\Models\Developer;
use App\Models\Facility;
use App\Models\Project;
use App\Models\ProjectBuilding;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ProjectService
{
    // ── Index ──────────────────────────────────────────────────────

    public function paginate(Request $request): LengthAwarePaginator
    {
        return Project::query()
            ->withCount('units')
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")
                                                  ->orWhere('location', 'like', "%{$request->search}%"))
            ->when($request->type,   fn($q) => $q->where('type',   $request->type))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(12)
            ->withQueryString()
            ->through(fn(Project $p) => [
                'id'               => $p->id,
                'name'             => $p->name,
                'project_code'     => $p->project_code,
                'theme_color'      => $p->theme_color,
                'type'             => $p->type->value,
                'type_label'       => $p->type->label(),
                'status'           => $p->status->value,
                'status_label'     => $p->status->label(),
                'location'         => $p->location,
                'total_units'      => $p->total_units,
                'units_count'      => $p->units_count,
                'overall_progress' => $p->overall_progress,
                'handover_date'    => $p->handover_date?->format('d M Y'),
                'cover'            => $p->getFirstMediaUrl('cover'),
            ]);
    }

    public function stats(): array
    {
        return [
            'total'              => Project::whereNotIn('status', [ProjectStatus::Draft->value])->count(),
            'draft'              => Project::where('status', ProjectStatus::Draft)->count(),
            'planning'           => Project::where('status', ProjectStatus::Planning)->count(),
            'under_construction' => Project::where('status', ProjectStatus::UnderConstruction)->count(),
            'completed'          => Project::where('status', ProjectStatus::Completed)->count(),
        ];
    }

    // ── Enums / reference data for forms ──────────────────────────

    public function enums(): array
    {
        return [
            'types' => collect(ProjectType::cases())->map(fn($t) => [
                'value' => $t->value,
                'label' => $t->label(),
            ])->values()->all(),

            'statuses' => collect(ProjectStatus::cases())->map(fn($e) => [
                'value' => $e->value,
                'label' => $e->label(),
            ])->values()->all(),

            'block_types' => [
                ['value' => 'residential', 'label' => 'Residential (Apartments)'],
                ['value' => 'villa',       'label' => 'Villas / Houses'],
                ['value' => 'commercial',  'label' => 'Commercial / Retail'],
                ['value' => 'office',      'label' => 'Office'],
                ['value' => 'industrial',  'label' => 'Industrial / Warehouse'],
                ['value' => 'mixed',       'label' => 'Mixed Use'],
            ],

            'themes' => ['#5B3DF5', '#1F9D6B', '#E0902B', '#E5484D', '#2A7DE1', '#475569'],

            'land_area_units' => ['katha', 'sqft', 'acres', 'marla'],

            'booking_amount_types' => [
                ['value' => 'fixed',      'label' => 'Fixed Amount (BDT)'],
                ['value' => 'percentage', 'label' => 'Percentage (%)'],
            ],

            'developers' => Developer::orderBy('name')
                ->get(['id', 'name'])
                ->toArray(),

            'facility_groups' => Facility::orderBy('sort_order')
                ->get()
                ->groupBy('group')
                ->map(fn($items, $group) => [
                    'group' => $group,
                    'items' => $items->map(fn($f) => [
                        'id'   => $f->id,
                        'name' => $f->name,
                        'icon' => $f->icon,
                    ])->values()->all(),
                ])
                ->values()
                ->all(),

            'block_spec_options' => [
                'lobby_types'  => ['Grand double-height', 'Standard', 'Shared', 'None'],
                'security'     => ['24/7 Manned', 'Biometric Access', 'Access Card', 'Smart Entry', 'None'],
                'generators'   => ['Full Backup', 'Partial Backup', 'Common Areas Only', 'None'],
                'hvac'         => ['Central Chiller', 'VRF / VRV', 'Split Units', 'VAV', 'District Cooling'],
                'cargo_access' => ['Dedicated Ramp', 'Shared Service Road', 'Rear Access', 'None'],
                'internet'     => ['Single ISP Fiber', 'Dual-Redundant Fiber', 'Leased Line', 'Not Provisioned'],
            ],

            'compliance_types'    => ['approval', 'certification'],
            'compliance_statuses' => ['pending', 'obtained', 'not_required'],
        ];
    }

    // ── Edit payload ───────────────────────────────────────────────

    public function forEdit(Project $project): array
    {
        $project->load(['buildings.sections', 'compliances']);

        return [
            'id'                  => $project->id,
            'name'                => $project->name,
            'project_code'        => $project->project_code,
            'theme_color'         => $project->theme_color,
            'type'                => $project->type->value,
            'status'              => $project->status->value,
            'developer_id'        => $project->developer_id,
            'developer_name'      => $project->developer_name,
            'description'         => $project->description,
            'location'            => $project->location,
            'address'             => $project->address,
            'latitude'            => $project->latitude,
            'longitude'           => $project->longitude,
            'start_date'          => $project->start_date?->format('Y-m-d'),
            'handover_date'       => $project->handover_date?->format('Y-m-d'),
            'land_area'           => $project->land_area,
            'land_area_unit'      => $project->land_area_unit,
            'built_up_area'       => $project->built_up_area,
            'estimated_value'     => $project->estimated_value,
            'booking_amount'      => $project->booking_amount,
            'booking_amount_type' => $project->booking_amount_type,
            'commission_pct'       => $project->commission_pct,
            'payment_plan_months'  => $project->payment_plan_months,
            'service_charge_sqft'  => $project->service_charge_sqft,
            'maintenance_years'    => $project->maintenance_years,

            'buildings' => $project->buildings->map(fn($b) => [
                'id'             => $b->id,
                'name'           => $b->name,
                'total_floors'   => $b->total_floors,
                'specifications' => $b->specifications ?: new \stdClass(),
                'sections'       => $b->sections->map(fn($s) => [
                    'id'             => $s->id,
                    'name'           => $s->name,
                    'type'           => $s->type,
                    'floor_start'    => $s->floor_start,
                    'floor_end'      => $s->floor_end,
                    'planned_units'  => $s->planned_units,
                    'is_block_unit'  => (bool) $s->is_block_unit,
                    'specifications' => $s->specifications ?: new \stdClass(),
                ])->toArray(),
            ])->toArray(),

            'facility_ids' => $project->facilities()->pluck('facilities.id')->toArray(),

            'compliances' => $project->compliances->map(fn($c) => [
                'id'            => $c->id,
                'name'          => $c->name,
                'type'          => $c->type,
                'status'        => $c->status,
                'obtained_date' => $c->obtained_date?->format('Y-m-d'),
            ])->toArray(),

            'cover'     => $project->getFirstMediaUrl('cover'),
            'images'    => $project->getMedia('images')->map(fn($m) => [
                'id'    => $m->id,
                'url'   => $m->getUrl(),
                'thumb' => $m->getUrl(),
                'name'  => $m->file_name,
            ])->toArray(),
            'documents' => $project->getMedia('documents')->map(fn($m) => [
                'id'   => $m->id,
                'url'  => $m->getUrl(),
                'name' => $m->file_name,
                'size' => $m->size,
                'mime' => $m->mime_type,
            ])->toArray(),
        ];
    }

    // ── Write operations ───────────────────────────────────────────

    public function create(array $data): Project
    {
        $data['slug'] = Str::slug($data['name']);

        [$buildings, $facilityIds, $compliances, $media, $attributes] = $this->extractRelated($data);

        $project = Project::create($attributes);

        $this->syncBuildings($project, $buildings);
        $this->syncFacilities($project, $facilityIds);
        $this->syncCompliances($project, $compliances);
        $this->attachMedia($project, $media);

        return $project;
    }

    public function update(Project $project, array $data): Project
    {
        $data['slug'] = Str::slug($data['name']);

        [$buildings, $facilityIds, $compliances, $media, $attributes] = $this->extractRelated($data);

        $project->update($attributes);

        $this->syncBuildings($project, $buildings);
        $this->syncFacilities($project, $facilityIds);
        $this->syncCompliances($project, $compliances);
        $this->attachMedia($project, $media);

        return $project->fresh();
    }

    public function delete(Project $project): string
    {
        $name = $project->name;
        $project->delete();

        return $name;
    }

    // ── Private helpers ────────────────────────────────────────────

    private function extractRelated(array $data): array
    {
        $relatedKeys = ['buildings', 'facility_ids', 'compliances'];
        $mediaKeys   = ['cover', 'remove_cover', 'new_images', 'remove_images', 'new_documents', 'remove_documents'];

        $buildings   = $data['buildings']    ?? [];
        $facilityIds = $data['facility_ids'] ?? [];
        $compliances = $data['compliances']  ?? [];
        $media       = Arr::only($data, $mediaKeys);
        $attributes  = Arr::except($data, array_merge($relatedKeys, $mediaKeys));

        return [$buildings, $facilityIds, $compliances, $media, $attributes];
    }

    private function syncBuildings(Project $project, array $buildings): void
    {
        if (empty($buildings)) {
            return;
        }

        $incomingIds = collect($buildings)->pluck('id')->filter()->all();
        $project->buildings()->whereNotIn('id', $incomingIds)->delete();

        foreach ($buildings as $i => $building) {
            $sections = $building['sections'] ?? [];
            $payload  = Arr::except($building, ['id', 'sections']) + ['sort_order' => $i];

            if (!empty($building['id'])) {
                $b = $project->buildings()->find($building['id']);
                $b?->update($payload);
            } else {
                $b = $project->buildings()->create($payload);
            }

            if ($b) {
                $this->syncSections($b, $sections, $project->id);
            }
        }
    }

    private function syncSections(ProjectBuilding $building, array $sections, int $projectId): void
    {
        $incomingIds = collect($sections)->pluck('id')->filter()->all();
        $building->sections()->whereNotIn('id', $incomingIds)->delete();

        foreach ($sections as $i => $section) {
            $payload = Arr::except($section, ['id']) + [
                'sort_order' => $i,
                'project_id' => $projectId,
            ];

            if (!empty($section['id'])) {
                $building->sections()->find($section['id'])?->update($payload);
            } else {
                $building->sections()->create($payload);
            }
        }
    }

    private function syncFacilities(Project $project, array $facilityIds): void
    {
        $project->facilities()->sync($facilityIds);
    }

    private function syncCompliances(Project $project, array $compliances): void
    {
        if (empty($compliances)) {
            return;
        }

        $incomingIds = collect($compliances)->pluck('id')->filter()->all();

        $project->compliances()->whereNotIn('id', $incomingIds)->delete();

        foreach ($compliances as $compliance) {
            $payload = Arr::except($compliance, ['id']);

            if (!empty($compliance['id'])) {
                $project->compliances()->find($compliance['id'])?->update($payload);
            } else {
                $project->compliances()->create($payload);
            }
        }
    }

    private function attachMedia(Project $project, array $data): void
    {
        if (!empty($data['cover'])) {
            $project->clearMediaCollection('cover');
            $project->addMedia($data['cover'])->toMediaCollection('cover');
        } elseif ($data['remove_cover'] ?? false) {
            $project->clearMediaCollection('cover');
        }

        foreach ($data['new_images'] ?? [] as $image) {
            $project->addMedia($image)->toMediaCollection('images');
        }
        foreach ($data['remove_images'] ?? [] as $mediaId) {
            $project->deleteMedia((int) $mediaId);
        }

        foreach ($data['new_documents'] ?? [] as $doc) {
            $project->addMedia($doc)->toMediaCollection('documents');
        }
        foreach ($data['remove_documents'] ?? [] as $mediaId) {
            $project->deleteMedia((int) $mediaId);
        }
    }
}
