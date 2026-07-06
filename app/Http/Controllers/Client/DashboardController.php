<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ProjectConstruction;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $client = Auth::guard('client')->user();

        // Latest booking for hero section (with media for cover image)
        $latestBooking = $client->bookings()
            ->with(['unit.media', 'unit.project.media'])
            ->latest('booking_date')
            ->first();

        // Resolve cover image: unit images → project cover → project images → null
        $coverImage = $latestBooking?->unit?->getFirstMediaUrl('images')
            ?: $latestBooking?->unit?->project?->getFirstMediaUrl('cover')
            ?: $latestBooking?->unit?->project?->getFirstMediaUrl('images')
            ?: null;

        // All bookings (max 4) with paid payments — reused for payment cards & project IDs
        $allBookings = $client->bookings()
            ->with(['unit.project', 'unit.media', 'payments' => fn($q) => $q->whereNotNull('paid_at')])
            ->latest('booking_date')
            ->take(4)
            ->get();

        // KPI aggregates
        $totalPropertyValue = (float) $client->bookings()->sum('price_agreed');
        $totalPaid          = (float) $allBookings->flatMap->payments->sum('amount');
        // Note: totalPaid here covers only max-4 bookings shown; for full accuracy recalculate:
        $totalPaid = (float) Payment::whereHas(
            'booking',
            fn($q) => $q->where('client_id', $client->id)
        )->whereNotNull('paid_at')->sum('amount');

        // Per-booking payment progress cards
        $paymentCards = $allBookings->map(fn($b) => [
            'id'           => $b->id,
            'project_name' => $b->unit?->project?->name ?? 'Unknown Project',
            'unit_number'  => $b->unit?->unit_number ?? '—',
            'price_agreed' => (float) $b->price_agreed,
            'total_paid'   => (float) $b->payments->sum('amount'),
        ])->values();

        // Construction progress per unique project
        $projectIds = $allBookings->pluck('unit.project_id')->unique()->filter()->values();
        $constructionCards = ProjectConstruction::whereIn('project_id', $projectIds)
            ->with('project')
            ->get()
            ->map(fn($c) => [
                'project_name'  => $c->project?->name ?? 'Unknown Project',
                'time_progress' => (float) ($c->time_progress ?? 0),
                'status'        => $c->status?->value ?? 'on_track',
                'status_label'  => $c->status?->label() ?? 'On Track',
                'handover_date' => $c->project?->handover_date?->format('M Y') ?? '—',
            ])->values();

        // Recent payments (last 5)
        $recentPayments = Payment::whereHas(
            'booking',
            fn($q) => $q->where('client_id', $client->id)
        )->with(['installment', 'booking.unit.project'])
            ->whereNotNull('paid_at')
            ->latest('paid_at')
            ->take(5)
            ->get()
            ->map(fn($p) => [
                'date'    => $p->paid_at->format('d M Y'),
                'label'   => $p->installment
                    ? 'Installment ' . $p->installment->installment_number
                    : 'Payment',
                'amount'  => (float) $p->amount,
                'project' => $p->booking?->unit?->project?->name ?? '—',
            ])->values();

        // Recommended projects — exclude ones the client already booked, max 4
        $bookedProjectIds = $allBookings->pluck('unit.project_id')->unique()->filter()->values()->toArray();
        $recommendedProjects = Project::whereNotIn('id', $bookedProjectIds)
            ->whereNotNull('published_at')
            ->with('media')
            ->latest()
            ->take(4)
            ->get()
            ->map(fn($p) => [
                'id'              => $p->id,
                'name'            => $p->name,
                'location'        => $p->location ?? '—',
                'type_label'      => $p->type?->label() ?? '—',
                'estimated_value' => (float) ($p->estimated_value ?? 0),
                'cover_image'     => $p->getFirstMediaUrl('cover')
                    ?: $p->getFirstMediaUrl('images')
                    ?: null,
            ])->values();

        return Inertia::render('Client/Dashboard', [
            'client'               => $client,
            'latestBooking'        => $latestBooking,
            'coverImage'           => $coverImage,
            'totalPropertyValue'   => $totalPropertyValue,
            'totalPaid'            => $totalPaid,
            'outstandingBalance'   => $totalPropertyValue - $totalPaid,
            'paymentCards'         => $paymentCards,
            'constructionCards'    => $constructionCards,
            'recentPayments'       => $recentPayments,
            'recommendedProjects'  => $recommendedProjects,
        ]);
    }
}
