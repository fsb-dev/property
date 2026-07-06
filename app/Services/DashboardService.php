<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\ProjectStatus;
use App\Enums\UnitStatus;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Installment;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Unit;
use Illuminate\Support\Carbon;

class DashboardService
{
    public function stats(): array
    {
        $now              = Carbon::now();
        $startOfMonth     = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonthNoOverflow()->startOfMonth();
        $endOfLastMonth   = $now->copy()->subMonthNoOverflow()->endOfMonth();

        $collectionThisMonth = (float) Payment::where('status', 'completed')
            ->whereBetween('paid_at', [$startOfMonth, $now])
            ->sum('amount');

        $collectionLastMonth = (float) Payment::where('status', 'completed')
            ->whereBetween('paid_at', [$startOfLastMonth, $endOfLastMonth])
            ->sum('amount');

        $collectionGrowthPct = $collectionLastMonth > 0
            ? round((($collectionThisMonth - $collectionLastMonth) / $collectionLastMonth) * 100, 1)
            : ($collectionThisMonth > 0 ? 100.0 : 0.0);

        $pendingAmount = (float) Installment::whereIn('status', ['pending', 'upcoming', 'overdue'])->sum('amount');

        $overdueAmount = (float) Installment::where(function ($q) use ($now) {
            $q->where('status', 'overdue')
                ->orWhere(function ($q2) use ($now) {
                    $q2->whereIn('status', ['pending', 'upcoming'])
                        ->where('due_date', '<', $now->toDateString());
                });
        })->sum('amount');

        return [
            'projects' => [
                'total'     => Project::whereNotIn('status', [ProjectStatus::Draft->value])->count(),
                'active'    => Project::whereIn('status', [ProjectStatus::Planning->value, ProjectStatus::UnderConstruction->value])->count(),
                'completed' => Project::where('status', ProjectStatus::Completed)->count(),
            ],
            'units' => [
                'total'     => Unit::count(),
                'sold'      => Unit::where('status', UnitStatus::Sold)->count(),
                'booked'    => Unit::where('status', UnitStatus::Booked)->count(),
                'available' => Unit::where('status', UnitStatus::Available)->count(),
            ],
            'clients' => [
                'total'          => Client::count(),
                'new_this_month' => Client::where('created_at', '>=', $startOfMonth)->count(),
            ],
            'collection' => [
                'this_month' => $collectionThisMonth,
                'growth_pct' => $collectionGrowthPct,
            ],
            'pending' => [
                'amount'  => $pendingAmount,
                'overdue' => $overdueAmount,
            ],
        ];
    }

    public function recentBookings(int $limit = 6): array
    {
        return Booking::query()
            ->whereNotIn('status', [BookingStatus::Draft->value])
            ->with([
                'client:id,name',
                'unit:id,project_id,unit_number,bedrooms,type,price',
                'unit.project:id,name',
            ])
            ->latest('booking_date')
            ->take($limit)
            ->get()
            ->map(fn (Booking $b) => [
                'id'           => $b->id,
                'buyer'        => $b->client?->name ?? '—',
                'initials'     => $this->initials($b->client?->name),
                'project'      => $b->unit?->project?->name ?? '—',
                'unit'         => $b->unit?->unit_number ?? '—',
                'type'         => $b->unit?->bedrooms ? $b->unit->bedrooms.' Bed' : ($b->unit?->type?->label() ?? '—'),
                'amount'       => (float) $b->price_agreed,
                'date'         => optional($b->booking_date)->format('d M Y'),
                'status'       => $b->status->label(),
                'status_color' => $b->status->color(),
            ])
            ->all();
    }

    public function projectProgress(int $limit = 5): array
    {
        return Project::whereNotIn('status', [ProjectStatus::Draft->value, ProjectStatus::Cancelled->value])
            ->orderByDesc('overall_progress')
            ->take($limit)
            ->get(['id', 'name', 'overall_progress'])
            ->map(fn (Project $p) => [
                'id'       => $p->id,
                'name'     => $p->name,
                'progress' => (float) ($p->overall_progress ?? 0),
            ])
            ->all();
    }

    public function salesInventory(): array
    {
        return [
            'sold'      => Unit::where('status', UnitStatus::Sold)->count(),
            'booked'    => Unit::where('status', UnitStatus::Booked)->count(),
            'available' => Unit::where('status', UnitStatus::Available)->count(),
            'total'     => Unit::count(),
        ];
    }

    public function paymentTrend(int $months = 5): array
    {
        $start = Carbon::now()->subMonths($months - 1)->startOfMonth();

        $payments = Payment::where('status', 'completed')
            ->where('paid_at', '>=', $start)
            ->get(['amount', 'paid_at']);

        $buckets = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $m = Carbon::now()->subMonths($i);
            $buckets[$m->format('Y-m')] = ['label' => $m->format('M Y'), 'amount' => 0.0];
        }

        foreach ($payments as $p) {
            $key = $p->paid_at?->format('Y-m');
            if ($key && isset($buckets[$key])) {
                $buckets[$key]['amount'] += (float) $p->amount;
            }
        }

        return array_values($buckets);
    }

    public function topPerforming(int $limit = 5): array
    {
        return Project::whereNotIn('status', [ProjectStatus::Draft->value])
            ->withCount([
                'units as sold_units_count'  => fn ($q) => $q->where('status', UnitStatus::Sold),
                'units as total_units_count',
            ])
            ->get()
            ->map(fn (Project $p) => [
                'id'         => $p->id,
                'name'       => $p->name,
                'sold'       => $p->sold_units_count,
                'total'      => $p->total_units_count,
                'conversion' => $p->total_units_count > 0
                    ? round($p->sold_units_count / $p->total_units_count * 100, 1)
                    : 0.0,
            ])
            ->sortByDesc('sold')
            ->take($limit)
            ->values()
            ->all();
    }

    private function initials(?string $name): string
    {
        if (! $name) {
            return '—';
        }

        $parts   = preg_split('/\s+/', trim($name));
        $letters = array_map(fn ($p) => mb_substr($p, 0, 1), array_slice($parts, 0, 2));

        return mb_strtoupper(implode('', $letters));
    }
}
