<?php

namespace App\Services;

use App\Enums\ProjectCategory;
use App\Enums\ProjectStatus;
use App\Enums\ProjectType;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
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
                'cover'            => $p->getFirstMediaUrl('cover', 'thumb'),
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
            'types'      => collect(ProjectType::cases())->map(fn($e) => ['value' => $e->value, 'label' => $e->label()]),
            'categories' => collect(ProjectCategory::cases())->map(fn($e) => ['value' => $e->value, 'label' => $e->label()]),
            'statuses'   => collect(ProjectStatus::cases())->map(fn($e) => ['value' => $e->value, 'label' => $e->label()]),
        ];
    }

    public function forEdit(Project $project): array
    {
        return [
            ...$project->toArray(),
            'type'          => $project->type->value,
            'category'      => $project->category?->value,
            'status'        => $project->status->value,
            'handover_date' => $project->handover_date?->format('Y-m-d'),
            'cover'         => $project->getFirstMediaUrl('cover', 'thumb'),
            'images'        => $project->getMedia('images')->map(fn($m) => [
                'id'    => $m->id,
                'url'   => $m->getUrl(),
                'thumb' => $m->getUrl('thumb'),
                'name'  => $m->file_name,
            ]),
        ];
    }

    public function create(array $data): Project
    {
        $data['slug'] = Str::slug($data['name']);

        return Project::create($data);
    }

    public function update(Project $project, array $data): Project
    {
        $data['slug'] = Str::slug($data['name']);
        $project->update($data);

        return $project->fresh();
    }

    public function delete(Project $project): string
    {
        $name = $project->name;
        $project->delete();

        return $name;
    }
}
