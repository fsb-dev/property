<?php

namespace App\Services;

use App\Enums\ProjectCategory;
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
            ->with('project:id,name')
            ->when($request->search,     fn($q) => $q->where('unit_number', 'like', "%{$request->search}%")
                                                      ->orWhere('block',       'like', "%{$request->search}%"))
            ->when($request->project_id, fn($q) => $q->where('project_id', $request->project_id))
            ->when($request->type,       fn($q) => $q->where('type',       $request->type))
            ->when($request->status,     fn($q) => $q->where('status',     $request->status))
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn(Unit $u) => [
                'id'           => $u->id,
                'unit_number'  => $u->unit_number,
                'block'        => $u->block,
                'floor'        => $u->floor,
                'project_id'   => $u->project_id,
                'project_name' => $u->project->name,
                'type'         => $u->type?->value,
                'type_label'   => $u->type?->label(),
                'bedrooms'     => $u->bedrooms,
                'size_sqft'    => $u->size_sqft,
                'view'         => $u->view,
                'price'        => (float) $u->price,
                'status'       => $u->status->value,
                'status_label' => $u->status->label(),
                'handover_date'=> $u->handover_date?->format('d M Y'),
                'floor_plan'   => $u->getFirstMediaUrl('floor_plan'),
            ]);
    }

    public function stats(): array
    {
        return [
            'total'     => Unit::count(),
            'available' => Unit::where('status', UnitStatus::Available)->count(),
            'reserved'  => Unit::where('status', UnitStatus::Reserved)->count(),
            'sold'      => Unit::where('status', UnitStatus::Sold)->count(),
        ];
    }

    public function enums(): array
    {
        return [
            'types'    => collect(UnitType::cases())->map(fn($e) => ['value' => $e->value, 'label' => $e->label()]),
            'statuses' => collect(UnitStatus::cases())->map(fn($e) => ['value' => $e->value, 'label' => $e->label()]),
        ];
    }

    public function projectsList(): array
    {
        return Project::orderBy('name')
            ->get(['id', 'name', 'category'])
            ->filter(function (Project $p) {
                // Exclude projects whose category explicitly has no unit support.
                // Projects with no category yet are included (still being set up).
                // Use getRawOriginal to get the plain string, bypassing the enum cast.
                $cat = ProjectCategory::tryFrom($p->getRawOriginal('category') ?? '');
                return $cat === null || $cat->hasUnits();
            })
            ->map(fn($p) => ['id' => $p->id, 'name' => $p->name])
            ->values()
            ->toArray();
    }

    public function forEdit(Unit $unit): array
    {
        return [
            ...$unit->toArray(),
            'type'          => $unit->type?->value,
            'status'        => $unit->status->value,
            'handover_date' => $unit->handover_date?->format('Y-m-d'),
            'floor_plan'    => $unit->getFirstMediaUrl('floor_plan'),
            'images'        => $unit->getMedia('images')->map(fn($m) => [
                'id'   => $m->id,
                'url'  => $m->getUrl(),
                'thumb'=> $m->getUrl(),
                'name' => $m->file_name,
            ])->toArray(),
            'documents'     => $unit->getMedia('documents')->map(fn($m) => [
                'id'   => $m->id,
                'url'  => $m->getUrl(),
                'name' => $m->file_name,
                'size' => $m->size,
                'mime' => $m->mime_type,
            ])->toArray(),
        ];
    }

    public function create(array $data): Unit
    {
        $mediaKeys  = ['floor_plan', 'new_images', 'remove_images', 'new_documents', 'remove_documents'];
        $media      = Arr::only($data, $mediaKeys);
        $attributes = Arr::except($data, $mediaKeys);

        $unit = Unit::create($attributes);
        $this->attachMedia($unit, $media);

        return $unit;
    }

    public function update(Unit $unit, array $data): Unit
    {
        $mediaKeys  = ['floor_plan', 'remove_floor_plan', 'new_images', 'remove_images', 'new_documents', 'remove_documents'];
        $media      = Arr::only($data, $mediaKeys);
        $attributes = Arr::except($data, $mediaKeys);

        $unit->update($attributes);
        $this->attachMedia($unit, $media);

        return $unit->fresh();
    }

    public function delete(Unit $unit): string
    {
        $label = "Unit {$unit->unit_number}";
        $unit->delete();

        return $label;
    }

    private function attachMedia(Unit $unit, array $data): void
    {
        if (!empty($data['floor_plan'])) {
            $unit->clearMediaCollection('floor_plan');
            $unit->addMedia($data['floor_plan'])->toMediaCollection('floor_plan');
        } elseif ($data['remove_floor_plan'] ?? false) {
            $unit->clearMediaCollection('floor_plan');
        }

        foreach ($data['new_images'] ?? [] as $image) {
            $unit->addMedia($image)->toMediaCollection('images');
        }
        foreach ($data['remove_images'] ?? [] as $mediaId) {
            $unit->deleteMedia((int) $mediaId);
        }

        foreach ($data['new_documents'] ?? [] as $doc) {
            $unit->addMedia($doc)->toMediaCollection('documents');
        }
        foreach ($data['remove_documents'] ?? [] as $mediaId) {
            $unit->deleteMedia((int) $mediaId);
        }
    }
}
