<?php

namespace App\Services;

use App\Enums\ConstructionStatus;
use App\Enums\ProjectStatus;
use App\Models\ConstructionMilestone;
use App\Models\Project;
use App\Models\ProjectBuilding;
use App\Models\ProjectConstruction;
use App\Models\SiteUpdate;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class ConstructionService
{
    private const ACTIVE_STATUSES = [ProjectStatus::Planning, ProjectStatus::UnderConstruction];

    // ── KPI row ────────────────────────────────────────────────────

    public function kpis(): array
    {
        $activeProjects = Project::whereIn('status', self::ACTIVE_STATUSES);
        $activeCount    = (clone $activeProjects)->count();
        $totalCount     = Project::whereNotIn('status', [ProjectStatus::Draft, ProjectStatus::Cancelled])->count();

        $buildings = ProjectBuilding::whereIn(
            'project_id',
            (clone $activeProjects)->pluck('id')
        )->count();

        $avgProgress = (float) (clone $activeProjects)->avg('overall_progress');

        $onTime  = ProjectConstruction::where('status', ConstructionStatus::OnTrack->value)->count();
        $delayed = ProjectConstruction::where('status', ConstructionStatus::Delayed->value)->count();

        $milestonesCompleted = ConstructionMilestone::where('status', 'completed')->count();
        $milestonesPending   = ConstructionMilestone::where('status', '!=', 'completed')->count();

        $constructedProjects = Project::has('construction')->with('construction')->get(['id', 'estimated_value']);
        $budgetTotal = (float) $constructedProjects->sum(fn (Project $p) => $p->estimated_value ?? $p->construction->budget_total ?? 0);
        $budgetUsed  = (float) $constructedProjects->sum(fn (Project $p) => $p->construction->budget_used ?? 0);

        return [
            'active_projects'  => $activeCount,
            'active_pct'       => $totalCount ? round($activeCount / $totalCount * 100) : 0,
            'buildings'        => $buildings,
            'overall_progress' => round($avgProgress, 1),
            'on_time'          => $onTime,
            'on_time_pct'      => $totalCount ? round($onTime / $totalCount * 100) : 0,
            'delayed'          => $delayed,
            'delayed_pct'      => $totalCount ? round($delayed / $totalCount * 100) : 0,
            'milestones_done'    => $milestonesCompleted,
            'milestones_pending' => $milestonesPending,
            'budget_total'     => $budgetTotal,
            'budget_used'      => $budgetUsed,
            'budget_used_pct'  => $budgetTotal ? round($budgetUsed / $budgetTotal * 100) : 0,
        ];
    }

    // ── Progress overview donut ───────────────────────────────────

    public function progressOverview(): array
    {
        $total = Project::whereNotIn('status', [ProjectStatus::Draft, ProjectStatus::Cancelled])->count();
        if (! $total) {
            return ['completed' => 0, 'in_progress' => 0, 'pending' => 0, 'delayed' => 0];
        }

        $completed  = Project::where('status', ProjectStatus::Completed)->count();
        $delayed    = ProjectConstruction::where('status', ConstructionStatus::Delayed->value)->count();
        $pending    = ProjectConstruction::where('status', ConstructionStatus::Paused->value)->count();
        $inProgress = max(0, $total - $completed - $delayed - $pending);

        return [
            'completed'   => round($completed / $total * 100, 1),
            'in_progress' => round($inProgress / $total * 100, 1),
            'pending'     => round($pending / $total * 100, 1),
            'delayed'     => round($delayed / $total * 100, 1),
        ];
    }

    // ── 6-month progress trend, derived from completed milestones ─

    public function progressTrend(): array
    {
        $totalMilestones = ConstructionMilestone::count();
        $months = collect(range(5, 0))->map(fn ($i) => Carbon::now()->subMonths($i)->startOfMonth());

        return $months->map(function (Carbon $month) use ($totalMilestones) {
            $completedByThen = $totalMilestones
                ? ConstructionMilestone::where('status', 'completed')
                    ->whereNotNull('completed_date')
                    ->where('completed_date', '<=', $month->copy()->endOfMonth())
                    ->count()
                : 0;

            return [
                'label' => $month->format('M Y'),
                'value' => $totalMilestones ? round($completedByThen / $totalMilestones * 100, 1) : 0,
            ];
        })->values()->all();
    }

    // ── Progress by project (top 5 active) ─────────────────────────

    public function progressByProject(): array
    {
        return Project::whereIn('status', self::ACTIVE_STATUSES)
            ->orderByDesc('overall_progress')
            ->limit(5)
            ->get(['id', 'name', 'overall_progress', 'theme_color'])
            ->map(fn (Project $p) => [
                'name'  => $p->name,
                'pct'   => (float) $p->overall_progress,
                'color' => $p->theme_color ?? '#5B3DF5',
            ])->all();
    }

    // ── Upcoming milestones ─────────────────────────────────────────

    public function upcomingMilestones(): array
    {
        return ConstructionMilestone::with('project:id,name')
            ->where('status', '!=', 'completed')
            ->whereNotNull('estimated_date')
            ->orderBy('estimated_date')
            ->limit(5)
            ->get()
            ->map(fn (ConstructionMilestone $m) => [
                'title'   => $m->name,
                'project' => $m->project?->name,
                'date'    => $m->estimated_date?->format('d M Y'),
            ])->all();
    }

    // ── Site activity feed ──────────────────────────────────────────

    public function siteActivity(): array
    {
        return SiteUpdate::with('project:id,name')
            ->latest('update_date')
            ->latest('id')
            ->limit(8)
            ->get()
            ->map(fn (SiteUpdate $u) => [
                'id'      => $u->id,
                'text'    => $u->title,
                'project' => $u->project?->name,
                'time'    => $u->update_date?->diffForHumans(),
                'photo'   => $u->getFirstMediaUrl('photo') ?: null,
            ])->all();
    }

    // ── Projects construction status table ──────────────────────────

    public function table(Request $request): LengthAwarePaginator
    {
        return Project::query()
            ->with('construction')
            ->whereNotIn('status', [ProjectStatus::Draft])
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('location', 'like', "%{$request->search}%"))
            ->orderByDesc('overall_progress')
            ->paginate(5)
            ->withQueryString()
            ->through(function (Project $project) {
                $c = $project->construction;
                $status = $c?->status ?? ConstructionStatus::OnTrack;
                $quality = (float) ($c?->quality_score ?? 0);

                $nextMilestone = $project->constructionMilestones()
                    ->where('status', '!=', 'completed')
                    ->whereNotNull('estimated_date')
                    ->orderBy('estimated_date')
                    ->first();

                $budgetTotal = (float) ($project->estimated_value ?? $c?->budget_total ?? 0);
                $budgetUsed  = (float) ($c?->budget_used ?? 0);

                return [
                    'id'          => $project->id,
                    'name'        => $project->name,
                    'location'    => $project->location,
                    'color'       => $project->theme_color ?? '#5B3DF5',
                    'overall'     => (float) $project->overall_progress,
                    'time'        => (float) ($c?->time_progress ?? 0),
                    'quality'     => $quality,
                    'quality_label' => $this->qualityLabel($quality),
                    'budget_total'  => $budgetTotal,
                    'budget_used'   => $budgetUsed,
                    'budget_pct'    => $budgetTotal ? round($budgetUsed / $budgetTotal * 100) : 0,
                    'status'        => $status->value,
                    'status_label'  => $status->label(),
                    'status_variant' => $status->variant(),
                    'milestone'     => $nextMilestone?->name ?? '—',
                    'target'        => $nextMilestone?->estimated_date?->format('d M Y') ?? '—',
                ];
            });
    }

    private function qualityLabel(float $score): string
    {
        return match (true) {
            $score >= 85 => 'Excellent',
            $score >= 70 => 'Good',
            $score >= 50 => 'Average',
            default      => 'Poor',
        };
    }

    // ── Reference data for the Add Site Update form ─────────────────

    public function projectOptions(): array
    {
        return Project::whereIn('status', self::ACTIVE_STATUSES)
            ->with('construction')
            ->orderBy('name')
            ->get(['id', 'name', 'overall_progress', 'estimated_value'])
            ->map(fn (Project $p) => [
                'id'               => $p->id,
                'name'             => $p->name,
                'overall_progress' => (float) $p->overall_progress,
                'time_progress'    => (float) ($p->construction?->time_progress ?? 0),
                'budget_total'     => (float) ($p->estimated_value ?? $p->construction?->budget_total ?? 0),
                'budget_used'      => (float) ($p->construction?->budget_used ?? 0),
            ])->all();
    }

    // ── Construction status options (for the status select) ─────────

    public function statusOptions(): array
    {
        return array_map(fn (ConstructionStatus $s) => [
            'value' => $s->value,
            'label' => $s->label(),
        ], ConstructionStatus::cases());
    }

    // ── All non-draft projects (for the "Add Status" project picker) ─

    public function allProjects(): array
    {
        return Project::whereNotIn('status', [ProjectStatus::Draft])
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Project $p) => ['id' => $p->id, 'name' => $p->name])
            ->all();
    }

    // ── Create/update a project's construction status row ────────────

    public function upsertConstructionStatus(array $data): ProjectConstruction
    {
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'homeverse'],
            ['name' => 'HomeVerse Real Estate', 'status' => 'active']
        );

        $construction = ProjectConstruction::updateOrCreate(
            ['project_id' => $data['project_id']],
            [
                'tenant_id'     => $tenant->id,
                'status'        => $data['status'],
                'time_progress' => $data['time_progress'],
                'quality_score' => $data['quality_score'],
                'budget_total'  => $data['budget_total'],
                'budget_used'   => $data['budget_used'],
            ]
        );

        return $construction;
    }

    // ── Delete a project's construction status row (resets to defaults) ─

    public function deleteConstructionStatus(int $projectId): void
    {
        ProjectConstruction::where('project_id', $projectId)->delete();
    }

    // ── Create a site update (with optional photo) ──────────────────

    public function createSiteUpdate(array $data): SiteUpdate
    {
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'homeverse'],
            ['name' => 'HomeVerse Real Estate', 'status' => 'active']
        );

        /** @var UploadedFile|null $photo */
        $photo = $data['photo'] ?? null;

        $siteUpdate = SiteUpdate::create([
            'tenant_id'   => $tenant->id,
            'project_id'  => $data['project_id'],
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'media_type'  => 'image',
            'update_date' => $data['update_date'],
        ]);

        if ($photo) {
            $siteUpdate->addMedia($photo)->toMediaCollection('photo');
        }

        // The site update's reported progress becomes the project's new overall progress.
        Project::whereKey($data['project_id'])->update(['overall_progress' => $data['progress']]);

        // Time progress and budget live on the construction status row (create it if this project doesn't have one yet).
        $construction = ProjectConstruction::firstOrNew(['project_id' => $data['project_id']]);
        $construction->tenant_id = $construction->tenant_id ?: $tenant->id;
        $construction->time_progress = $data['time_progress'];
        $construction->budget_total  = $data['budget_total'];
        $construction->budget_used   = $data['budget_used'];
        $construction->save();

        return $siteUpdate;
    }
}
