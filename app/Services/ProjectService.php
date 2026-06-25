<?php

namespace App\Services;

use App\Enums\ProjectStatus;
use App\Enums\ProjectType;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ProjectService
{
    public function paginate(Request $request): LengthAwarePaginator
    {
        return Project::query()
            ->withCount('units')
            ->when($request->search, fn($q) => $q->where('name',     'like', "%{$request->search}%")
                                                  ->orWhere('location', 'like', "%{$request->search}%"))
            ->when($request->type,   fn($q) => $q->where('type',   $request->type))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(12)
            ->withQueryString()
            ->through(fn(Project $p) => [
                'id'               => $p->id,
                'name'             => $p->name,
                'type'             => $p->type->value,
                'type_label'       => $p->type->label(),
                'category'         => $p->category?->value,
                'category_label'   => $p->category?->label(),
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
            'total'              => Project::count(),
            'planning'           => Project::where('status', ProjectStatus::Planning)->count(),
            'under_construction' => Project::where('status', ProjectStatus::UnderConstruction)->count(),
            'completed'          => Project::where('status', ProjectStatus::Completed)->count(),
        ];
    }

    public function enums(): array
    {
        return [
            'types' => collect(ProjectType::cases())->map(fn($t) => [
                'value'      => $t->value,
                'label'      => $t->label(),
                'categories' => collect($t->categories())->map(fn($c) => [
                    'value'      => $c->value,
                    'label'      => $c->label(),
                    'hasUnits'   => $c->hasUnits(),
                    'specFields' => $c->specFields(),
                    'facilities' => $c->facilities(),
                ])->values()->all(),
            ])->values()->all(),
            'statuses' => collect(ProjectStatus::cases())->map(fn($e) => ['value' => $e->value, 'label' => $e->label()]),
        ];
    }

    public function forEdit(Project $project): array
    {
        return [
            ...$project->toArray(),
            'type'           => $project->type->value,
            'category'       => $project->category?->value,
            'status'         => $project->status->value,
            'handover_date'  => $project->handover_date?->format('Y-m-d'),
            'specifications' => $project->specifications ?? [],
            'facilities'    => $project->facilities ?? [],
            'cover'         => $project->getFirstMediaUrl('cover'),
            'images'        => $project->getMedia('images')->map(fn($m) => [
                'id'    => $m->id,
                'url'   => $m->getUrl(),
                'thumb' => $m->getUrl(),
                'name'  => $m->file_name,
            ])->toArray(),
            'documents'     => $project->getMedia('documents')->map(fn($m) => [
                'id'   => $m->id,
                'url'  => $m->getUrl(),
                'name' => $m->file_name,
                'size' => $m->size,
                'mime' => $m->mime_type,
            ])->toArray(),
        ];
    }

    public function create(array $data): Project
    {
        $data['slug'] = Str::slug($data['name']);

        $mediaKeys  = ['cover', 'new_images', 'remove_images', 'remove_cover', 'new_documents', 'remove_documents'];
        $media      = Arr::only($data, $mediaKeys);
        $attributes = Arr::except($data, $mediaKeys);

        $project = Project::create($attributes);
        $this->attachMedia($project, $media);

        return $project;
    }

    public function update(Project $project, array $data): Project
    {
        $data['slug'] = Str::slug($data['name']);

        $mediaKeys  = ['cover', 'new_images', 'remove_images', 'remove_cover', 'new_documents', 'remove_documents'];
        $media      = Arr::only($data, $mediaKeys);
        $attributes = Arr::except($data, $mediaKeys);

        $project->update($attributes);
        $this->attachMedia($project, $media);

        return $project->fresh();
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

    public function delete(Project $project): string
    {
        $name = $project->name;
        $project->delete();

        return $name;
    }
}
