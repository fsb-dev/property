<?php

namespace App\Http\Controllers\Client;

use App\Enums\UnitStatus;
use App\Http\Controllers\Controller;
use App\Models\Project;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function show(Project $project): Response
    {
        $project->load(['facilities', 'compliances', 'construction', 'developer', 'units' => fn ($q) => $q->orderBy('floor')->orderBy('unit_number')]);

        $unitStats = [
            'total'     => $project->units->count(),
            'available' => $project->units->where('status', UnitStatus::Available)->count(),
            'sold'      => $project->units->where('status', UnitStatus::Sold)->count(),
            'reserved'  => $project->units->where('status', UnitStatus::Booked)->count(),
        ];

        $units = $project->units->map(fn ($u) => [
            'id'           => $u->id,
            'unit_number'  => $u->unit_number,
            'floor'        => $u->floor,
            'block'        => $u->block,
            'type_label'   => $u->type?->label() ?? '—',
            'bedrooms'     => $u->bedrooms,
            'bathrooms'    => $u->bathrooms,
            'size_sqft'    => $u->size_sqft,
            'view'         => $u->view,
            'price'        => (float) ($u->current_price ?? $u->price ?? 0),
            'status'       => $u->status?->value,
            'status_label' => $u->status?->label() ?? '—',
            'status_color' => $u->status?->color() ?? 'slate',
        ])->values();

        return Inertia::render('Client/Projects/Show', [
            'project' => [
                'id'               => $project->id,
                'name'             => $project->name,
                'project_code'     => $project->project_code,
                'type_label'       => $project->type->label(),
                'status_label'     => $project->status->label(),
                'status'           => $project->status->value,
                'description'      => $project->description,
                'location'         => $project->location,
                'address'          => $project->address,
                'start_date'       => $project->start_date?->format('d M Y'),
                'handover_date'    => $project->handover_date?->format('d M Y'),
                'total_floors'     => $project->total_floors,
                'total_units'      => $project->total_units,
                'land_area'        => $project->land_area,
                'land_area_unit'   => $project->land_area_unit,
                'built_up_area'    => $project->built_up_area,
                'estimated_value'  => $project->estimated_value,
                'overall_progress' => $project->overall_progress,
                'developer_name'   => $project->developer?->name ?? $project->developer_name,
                'theme_color'      => $project->theme_color ?? '#5B3DF5',
                'cover'            => $project->getFirstMediaUrl('cover'),
                'images'           => $project->getMedia('images')->map(fn($m) => $m->getUrl())->values(),
                'facilities'       => $project->facilities->map(fn($f) => [
                    'name' => $f->name,
                    'icon' => $f->icon,
                ])->values(),
                'compliances'      => $project->compliances->map(fn($c) => [
                    'name'   => $c->name,
                    'type'   => $c->type,
                    'status' => $c->status,
                ])->values(),
                'unit_stats'       => $unitStats,
                'units'            => $units,
                'construction_pct' => $project->construction?->overall_pct ?? $project->overall_progress ?? 0,
            ],
        ]);
    }
}
