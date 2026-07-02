<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProjectStatus;
use App\Http\Controllers\Controller;
use App\Models\Project;

class InvestmentController extends Controller
{
    /**
     * Investment Data has no dedicated financial model yet, so KPIs, charts, and
     * chart-adjacent widgets are rendered from a static JSON fixture — but the
     * "Project Investment Overview" table lists real projects from the database,
     * each paired with a demo set of investment figures (project_metrics in the
     * JSON) since the projects table doesn't carry ROI/rental/occupancy columns.
     */
    public function index()
    {
        $data = json_decode(file_get_contents(resource_path('data/investment.json')), true);

        $metrics = $data['project_metrics'];
        unset($data['project_metrics']);

        $data['projects'] = Project::query()
            ->whereNotIn('status', [ProjectStatus::Draft->value])
            ->latest()
            ->get()
            ->values()
            ->map(function (Project $project, int $i) use ($metrics) {
                $metric = $metrics[$i % count($metrics)];

                return [
                    'id'         => $project->id,
                    'name'       => $project->name,
                    'location'   => $project->location ?: ($project->address ?: '—'),
                    'type'       => $project->type->label(),
                    'status'     => $project->status->label(),
                    'investment' => $metric['investment'],
                    'value'      => $metric['value'],
                    'roi'        => $metric['roi'],
                    'rental'     => $metric['rental'],
                    'occupancy'  => $metric['occupancy'],
                    'demand'     => $metric['demand'],
                    'growth_yoy' => $metric['growth_yoy'],
                ];
            })
            ->all();

        return inertia('Admin/investment/Index', $data);
    }
}
