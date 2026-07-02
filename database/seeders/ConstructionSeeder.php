<?php

namespace Database\Seeders;

use App\Enums\ConstructionStatus;
use App\Enums\ProjectStatus;
use App\Models\ConstructionMilestone;
use App\Models\Project;
use App\Models\ProjectConstruction;
use App\Models\SiteUpdate;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

// Seeds construction status, milestones and site updates for every project
// created by ProjectSeeder, so the Construction Updates dashboard has real,
// coherent numbers (KPIs, trend, status table) instead of an empty state.
class ConstructionSeeder extends Seeder
{
    private array $milestoneNames = [
        'Land Clearance & Survey',
        'Foundation Work',
        'Structure Completion',
        'Exterior Work & Brickwork',
        'MEP Installation',
        'Finishing Work',
    ];

    private array $activityTemplates = [
        'Concrete casting completed for upper floor',
        'Excavation and foundation work in progress',
        'Steel structure installation completed',
        'MEP duct and conduit installation started',
        'Brickwork and exterior cladding progressing',
        'Piling work completed',
        'Interior finishing and painting underway',
        'Site inspection and quality audit completed',
    ];

    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'homeverse'],
            ['name' => 'HomeVerse Real Estate', 'status' => 'active']
        );

        $projects = Project::all();
        if ($projects->isEmpty()) {
            return;
        }

        foreach ($projects as $i => $project) {
            $this->seedConstruction($tenant->id, $project, $i);
            $this->seedMilestones($tenant->id, $project);
            $this->seedSiteUpdates($tenant->id, $project, $i);
        }
    }

    private function seedConstruction(int $tenantId, Project $project, int $i): void
    {
        $progress = (float) $project->overall_progress;

        [$status, $timeProgress, $quality] = match (true) {
            $project->status === ProjectStatus::Completed => [ConstructionStatus::Completed, 100.0, rand(88, 97) + 0.0],
            $project->status === ProjectStatus::OnHold     => [ConstructionStatus::Paused, max(0, $progress - 15), rand(55, 70) + 0.0],
            $project->status === ProjectStatus::Planning    => [
                $i % 3 === 0 ? ConstructionStatus::Inspection : ConstructionStatus::OnTrack,
                min(100, $progress + rand(-2, 4)),
                rand(60, 80) + 0.0,
            ],
            default => [ // under_construction
                $i % 4 === 0 ? ConstructionStatus::Delayed : ConstructionStatus::OnTrack,
                $i % 4 === 0 ? min(100, $progress + rand(8, 14)) : max(0, $progress + rand(-6, 4)),
                rand(72, 94) + 0.0,
            ],
        };

        $budgetTotal = $project->estimated_value
            ? round($project->estimated_value * 0.55, 2)
            : rand(80, 300) * 1_000_000;

        $budgetUsed = round($budgetTotal * (min(100, $progress + rand(-4, 2)) / 100), 2);

        ProjectConstruction::updateOrCreate(
            ['project_id' => $project->id],
            [
                'tenant_id'     => $tenantId,
                'status'        => $status->value,
                'time_progress' => round($timeProgress, 2),
                'quality_score' => round($quality, 2),
                'budget_total'  => $budgetTotal,
                'budget_used'   => max(0, $budgetUsed),
            ]
        );
    }

    private function seedMilestones(int $tenantId, Project $project): void
    {
        ConstructionMilestone::where('project_id', $project->id)->delete();

        $progress    = (float) $project->overall_progress;
        $count       = count($this->milestoneNames);
        $fraction    = $progress / 100;
        $start       = $project->start_date ?? Carbon::now()->subMonths(12);
        $end         = $project->handover_date ?? $start->copy()->addMonths(24);
        $totalDays   = max(1, $start->diffInDays($end));
        $pendingUsed = false;

        foreach ($this->milestoneNames as $index => $name) {
            $milestoneProgress = max(0, min(100, ($fraction * $count - $index) * 100));

            if ($milestoneProgress >= 100) {
                $status = 'completed';
            } elseif ($milestoneProgress > 0) {
                $status = 'in_progress';
            } elseif (! $pendingUsed) {
                $status = 'pending';
                $pendingUsed = true;
            } else {
                $status = 'not_started';
            }

            $estimatedDate = $start->copy()->addDays((int) round($totalDays * ($index + 1) / $count));
            $completedDate = $status === 'completed' ? $estimatedDate->copy()->subDays(rand(0, 5)) : null;

            ConstructionMilestone::create([
                'tenant_id'      => $tenantId,
                'project_id'     => $project->id,
                'name'           => $name,
                'description'    => $name.' for '.$project->name,
                'progress'       => round($milestoneProgress, 2),
                'status'         => $status,
                'sort_order'     => $index,
                'estimated_date' => $estimatedDate,
                'completed_date' => $completedDate,
            ]);
        }
    }

    private function seedSiteUpdates(int $tenantId, Project $project, int $i): void
    {
        SiteUpdate::where('project_id', $project->id)->delete();

        $count = $project->status === ProjectStatus::Completed ? 2 : 4;

        for ($n = 0; $n < $count; $n++) {
            $template = $this->activityTemplates[($i + $n) % count($this->activityTemplates)];

            SiteUpdate::create([
                'tenant_id'   => $tenantId,
                'project_id'  => $project->id,
                'title'       => $template,
                'description' => $template.' at '.$project->name.'. Crew and site engineers confirmed progress against the current milestone schedule.',
                'media_type'  => 'image',
                'update_date' => Carbon::now()->subDays(rand(0, 45) + ($n * 3)),
            ]);
        }
    }
}
