<?php

namespace App\Http\Controllers\Client;

use App\Enums\BookingStatus;
use App\Enums\UnitStatus;
use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PropertiesController extends Controller
{
    public function index(): Response
    {
        $client = Auth::guard('client')->user();

        $bookings = $client->bookings()
            ->with([
                'unit.project.media',
                'unit.project.construction',
                'unit.media',
                'payments' => fn ($q) => $q->whereNotNull('paid_at'),
            ])
            ->latest('booking_date')
            ->get();

        $properties = $bookings->map(fn ($b) => [
            'id'                  => $b->id,
            'status'              => $b->status instanceof BookingStatus ? $b->status->value : (string) $b->status,
            'status_label'        => $b->status instanceof BookingStatus ? $b->status->label() : ucfirst((string) $b->status),
            'booking_date'        => $b->booking_date?->format('d M Y'),
            'price_agreed'        => (float) $b->price_agreed,
            'total_paid'          => (float) $b->payments->sum('amount'),
            'unit_number'         => $b->unit?->unit_number ?? '—',
            'block'               => $b->unit?->block ?? null,
            'wing'                => $b->unit?->wing ?? null,
            'floor'               => $b->unit?->floor,
            'type_label'          => $b->unit?->type?->label() ?? '—',
            'bedrooms'            => $b->unit?->bedrooms,
            'bathrooms'           => $b->unit?->bathrooms,
            'size_sqft'           => $b->unit?->size_sqft,
            'view'                => $b->unit?->view,
            'project_name'        => $b->unit?->project?->name ?? '—',
            'project_location'    => $b->unit?->project?->location ?? '—',
            'handover_date'       => $b->unit?->project?->handover_date?->format('F Y') ?? '—',
            'construction_pct'    => (float) ($b->unit?->project?->construction?->time_progress ?? 0),
            'construction_status' => $b->unit?->project?->construction?->status?->value ?? 'on_track',
            'construction_label'  => $b->unit?->project?->construction?->status?->label() ?? 'On Track',
            'cover_image'         => $b->unit?->getFirstMediaUrl('images')
                ?: $b->unit?->project?->getFirstMediaUrl('cover')
                ?: $b->unit?->project?->getFirstMediaUrl('images')
                ?: null,
        ])->values();

        $purchasedCount  = $bookings->where('status', BookingStatus::Purchased)->count();
        $reservedCount   = $bookings->where('status', BookingStatus::Reserved)->count();
        $handedOverCount = $bookings->where('status', BookingStatus::HandedOver)->count();

        // Random available units as dummy favourites (no favourites feature yet)
        $favouriteUnits = Unit::where('status', UnitStatus::Available)
            ->with(['project.media', 'media'])
            ->inRandomOrder()
            ->take(4)
            ->get()
            ->map(fn ($u) => [
                'id'           => $u->id,
                'project_name' => $u->project?->name ?? '—',
                'location'     => $u->project?->location ?? '—',
                'unit_number'  => $u->unit_number ?? '—',
                'block'        => $u->block ?? null,
                'floor'        => $u->floor,
                'type_label'   => $u->type?->label() ?? '—',
                'bedrooms'     => $u->bedrooms,
                'size_sqft'    => $u->size_sqft,
                'price'        => (float) ($u->price ?? 0),
                'cover_image'  => $u->getFirstMediaUrl('images')
                    ?: $u->project?->getFirstMediaUrl('cover')
                    ?: $u->project?->getFirstMediaUrl('images')
                    ?: null,
            ])->values();

        // Upcoming handover — nearest booking that has a future handover date
        $upcoming = $bookings
            ->filter(fn ($b) => $b->status instanceof BookingStatus &&
                in_array($b->status, [BookingStatus::Purchased, BookingStatus::Reserved]) &&
                $b->unit?->project?->handover_date
            )
            ->sortBy(fn ($b) => $b->unit->project->handover_date)
            ->first();

        $upcomingHandover = null;
        if ($upcoming) {
            $hDate           = $upcoming->unit->project->handover_date;
            $upcomingHandover = [
                'project_name'   => $upcoming->unit->project->name,
                'unit_number'    => $upcoming->unit->unit_number ?? '—',
                'handover_date'  => $hDate->format('F Y'),
                'days_remaining' => $hDate->isFuture() ? (int) now()->diffInDays($hDate) : 0,
                'is_overdue'     => $hDate->isPast(),
                'cover_image'    => $upcoming->unit->getFirstMediaUrl('images')
                    ?: $upcoming->unit->project->getFirstMediaUrl('cover')
                    ?: $upcoming->unit->project->getFirstMediaUrl('images')
                    ?: null,
            ];
        }

        return Inertia::render('Client/Properties', [
            'properties'      => $properties,
            'purchasedCount'  => $purchasedCount,
            'reservedCount'   => $reservedCount,
            'handedOverCount' => $handedOverCount,
            'favouriteUnits'  => $favouriteUnits,
            'upcomingHandover'=> $upcomingHandover,
        ]);
    }
}
