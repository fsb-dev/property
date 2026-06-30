<?php

namespace App\Services;

use App\Enums\UnitStatus;
use App\Enums\UnitType;
use App\Models\Project;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class UnitService
{
    public function paginate(Request $request): LengthAwarePaginator
    {
        return Unit::query()
            ->with(['project:id,name', 'section:id,name'])
            ->when($request->search,     fn($q) => $q->where('unit_number', 'like', "%{$request->search}%"))
            ->when($request->project_id, fn($q) => $q->where('project_id', $request->project_id))
            ->when($request->block_id,   fn($q) => $q->where('block_id',   $request->block_id))
            ->when($request->type,       fn($q) => $q->where('type',       $request->type))
            ->when($request->status,     fn($q) => $q->where('status',     $request->status))
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn(Unit $u) => [
                'id'            => $u->id,
                'unit_number'   => $u->unit_number,
                'unit_code'     => $u->unit_code,
                'floor'         => $u->floor,
                'wing'          => $u->wing,
                'project_id'    => $u->project_id,
                'project_name'  => $u->project->name,
                'section_id'    => $u->block_id,
                'section_name'  => $u->section?->name,
                'type'          => $u->type?->value,
                'type_label'    => $u->type?->label(),
                'bedrooms'      => $u->bedrooms,
                'size_sqft'     => $u->size_sqft,
                'price'         => $u->price ? (float) $u->price : null,
                'status'        => $u->status->value,
                'status_label'  => $u->status->label(),
                'handover_date' => $u->handover_date?->format('d M Y'),
            ]);
    }

    public function stats(): array
    {
        return [
            'total'     => Unit::count(),
            'available' => Unit::where('status', UnitStatus::Available)->count(),
            'booked'    => Unit::where('status', UnitStatus::Booked)->count(),
            'sold'      => Unit::where('status', UnitStatus::Sold)->count(),
        ];
    }

    public function enums(): array
    {
        return [
            'types'    => collect(UnitType::cases())->map(fn($e) => ['value' => $e->value, 'label' => $e->label()]),
            'statuses' => collect(UnitStatus::cases())->map(fn($e) => ['value' => $e->value, 'label' => $e->label()]),
            'views'    => ['Sea View', 'Lake View', 'Garden View', 'City View', 'Pool View', 'Street View', 'Open View'],
            'facing'   => ['North', 'South', 'East', 'West', 'North-East', 'North-West', 'South-East', 'South-West'],
            'banks'    => ['City Bank', 'BRAC Bank', 'Eastern Bank', 'Dutch-Bangla Bank', 'Islami Bank', 'Standard Chartered', 'Prime Bank'],
        ];
    }

    public function projectsList(): array
    {
        return Project::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn($p) => ['id' => $p->id, 'name' => $p->name])
            ->toArray();
    }

    /** All sections grouped-friendly for the filter dropdown — [{id, name, project_id, label}] */
    public function sectionsList(): array
    {
        return \App\Models\ProjectBlock::query()
            ->with('building:id,name')
            ->orderBy('project_id')
            ->orderBy('name')
            ->get(['id', 'project_id', 'building_id', 'name'])
            ->map(fn($s) => [
                'id'         => $s->id,
                'project_id' => $s->project_id,
                'name'       => $s->name,
                'label'      => ($s->building?->name ? $s->building->name . ' — ' : '') . $s->name,
            ])
            ->toArray();
    }

    /** Full data for the 8-tab configure page */
    public function forConfigure(Unit $unit): array
    {
        $unit->load(['project:id,name', 'section.building:id,name']);

        return [
            // Context (read-only in UI)
            'id'             => $unit->id,
            'unit_number'    => $unit->unit_number,
            'project_id'     => $unit->project_id,
            'project_name'   => $unit->project?->name,
            'block_id'       => $unit->block_id,
            'section_name'   => $unit->section?->name,
            'building_name'  => $unit->section?->building?->name,
            'floor'          => $unit->floor,
            'floor_end'      => $unit->floor_end,
            'section_type'   => $unit->section?->type,

            // Identity
            'unit_code'      => $unit->unit_code,
            'type'           => $unit->type?->value,
            'wing'           => $unit->wing,
            'description'    => $unit->description,

            // Specifications
            'bedrooms'       => $unit->bedrooms,
            'bathrooms'      => $unit->bathrooms,
            'balconies'      => $unit->balconies,
            'servant_room'   => $unit->servant_room,
            'store_room'     => $unit->store_room,
            'parking_spaces' => $unit->parking_spaces,
            'facing_direction'=> $unit->facing_direction,
            'view'           => $unit->view,

            // Measurements
            'size_sqft'           => $unit->size_sqft,
            'super_built_up_area' => $unit->super_built_up_area,
            'carpet_area'         => $unit->carpet_area,
            'ceiling_height'      => $unit->ceiling_height,
            'terrace_area'        => $unit->terrace_area,
            'parking_area'        => $unit->parking_area,

            // Pricing
            'price'             => $unit->price ? (float) $unit->price : null,
            'launch_price'      => $unit->launch_price ? (float) $unit->launch_price : null,
            'current_price'     => $unit->current_price ? (float) $unit->current_price : null,
            'parking_price'     => $unit->parking_price ? (float) $unit->parking_price : null,
            'registration_fee'  => $unit->registration_fee ? (float) $unit->registration_fee : null,
            'vat_pct'           => $unit->vat_pct ? (float) $unit->vat_pct : null,
            'monthly_maintenance'=> $unit->monthly_maintenance ? (float) $unit->monthly_maintenance : null,
            'booking_amount'    => $unit->booking_amount ? (float) $unit->booking_amount : null,

            // Media URLs
            'video_url'         => $unit->video_url,
            'tour_360_url'      => $unit->tour_360_url,

            // Availability
            'status'         => $unit->status->value,
            'launch_date'    => $unit->launch_date?->format('Y-m-d'),
            'available_date' => $unit->available_date?->format('Y-m-d'),
            'handover_date'  => $unit->handover_date?->format('Y-m-d'),

            // Mortgage
            'eligible_banks'      => $unit->eligible_banks ?? [],
            'max_loan_amount'     => $unit->max_loan_amount ? (float) $unit->max_loan_amount : null,
            'payment_plan_months' => $unit->payment_plan_months,

            // Type-specific specs
            'specs'               => $unit->specs ?? [],

            // Media files
            'floor_plan'      => $unit->getFirstMediaUrl('floor_plan'),
            'floor_plan_pdf'  => $unit->getFirstMediaUrl('floor_plan_pdf'),
            'cad_dwg'         => $unit->getFirstMediaUrl('cad_dwg'),
            'images'          => $unit->getMedia('images')->map(fn($m) => [
                'id' => $m->id, 'url' => $m->getUrl(), 'name' => $m->file_name,
            ])->toArray(),
            'drone'           => $unit->getMedia('drone')->map(fn($m) => [
                'id' => $m->id, 'url' => $m->getUrl(), 'name' => $m->file_name,
            ])->toArray(),
            'interior'        => $unit->getMedia('interior')->map(fn($m) => [
                'id' => $m->id, 'url' => $m->getUrl(), 'name' => $m->file_name,
            ])->toArray(),
        ];
    }

    public function forEdit(Unit $unit): array
    {
        return $this->forConfigure($unit);
    }

    public function create(array $data): Unit
    {
        $mediaKeys  = ['floor_plan', 'floor_plan_pdf', 'cad_dwg', 'new_images', 'remove_images', 'new_drone', 'new_interior'];
        $media      = Arr::only($data, $mediaKeys);
        $attributes = Arr::except($data, $mediaKeys);

        if (isset($attributes['specs'])) {
            $attributes['specs'] = $this->castSpecs($attributes['specs']);
        }

        $unit = Unit::create($attributes);
        $this->attachMedia($unit, $media);

        return $unit;
    }

    public function update(Unit $unit, array $data): Unit
    {
        $mediaKeys  = [
            'floor_plan', 'remove_floor_plan',
            'floor_plan_pdf', 'remove_floor_plan_pdf',
            'cad_dwg', 'remove_cad_dwg',
            'new_images', 'remove_images',
            'new_drone', 'remove_drone',
            'new_interior', 'remove_interior',
        ];
        $media      = Arr::only($data, $mediaKeys);
        $attributes = Arr::except($data, $mediaKeys);

        if (isset($attributes['specs'])) {
            $attributes['specs'] = $this->castSpecs($attributes['specs']);
        }

        $unit->update($attributes);
        $this->attachMedia($unit, $media);

        // Auto-promote not_configured → configured when meaningful data is saved
        if ($unit->fresh()->status === UnitStatus::NotConfigured) {
            $unit->update(['status' => UnitStatus::Configured]);
        }

        return $unit->fresh();
    }

    private function castSpecs(array $specs): array
    {
        $booleans = ['living_room', 'dining_room', 'kitchen', 'pantry', 'server_room',
                     'cold_storage', 'temperature_control', 'fire_safety', 'truck_access'];
        $integers = ['master_bedrooms', 'master_bathrooms',
                     'num_cabins', 'meeting_rooms', 'num_gates', 'electric_load',
                     'storage_capacity', 'loading_capacity'];
        $floats   = ['front_width', 'display_area', 'loading_area', 'open_space'];

        $result = [];
        foreach ($specs as $key => $value) {
            if ($value === '' || $value === null) continue;
            if (in_array($key, $booleans))  { $result[$key] = filter_var($value, FILTER_VALIDATE_BOOLEAN); continue; }
            if (in_array($key, $integers))  { $result[$key] = (int) $value; continue; }
            if (in_array($key, $floats))    { $result[$key] = (float) $value; continue; }
            $result[$key] = $value;
        }

        return $result ?: [];
    }

    public function delete(Unit $unit): string
    {
        $label = "Unit {$unit->unit_number}";
        $unit->delete();
        return $label;
    }

    private function attachMedia(Unit $unit, array $data): void
    {
        $singleCollections = [
            'floor_plan'     => 'floor_plan',
            'floor_plan_pdf' => 'floor_plan_pdf',
            'cad_dwg'        => 'cad_dwg',
        ];

        foreach ($singleCollections as $key => $collection) {
            if (!empty($data[$key])) {
                $unit->clearMediaCollection($collection);
                $unit->addMedia($data[$key])->toMediaCollection($collection);
            } elseif ($data["remove_{$key}"] ?? false) {
                $unit->clearMediaCollection($collection);
            }
        }

        $multiCollections = [
            'new_images'   => 'images',
            'new_drone'    => 'drone',
            'new_interior' => 'interior',
        ];

        foreach ($multiCollections as $key => $collection) {
            foreach ($data[$key] ?? [] as $file) {
                $unit->addMedia($file)->toMediaCollection($collection);
            }
        }

        foreach (['remove_images', 'remove_drone', 'remove_interior'] as $key) {
            foreach ($data[$key] ?? [] as $mediaId) {
                $unit->deleteMedia((int) $mediaId);
            }
        }
    }
}
