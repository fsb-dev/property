<?php

namespace App\Http\Controllers\Client;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class MortgageController extends Controller
{
    public function index(): Response
    {
        $client = Auth::guard('client')->user();

        $bookings = $client->bookings()
            ->whereIn('status', [
                BookingStatus::Purchased->value,
                BookingStatus::Reserved->value,
                BookingStatus::HandedOver->value,
            ])
            ->with(['unit.project.media', 'unit.media'])
            ->latest('booking_date')
            ->get()
            ->map(fn ($b) => [
                'id'               => $b->id,
                'project_name'     => $b->unit?->project?->name ?? '—',
                'project_location' => $b->unit?->project?->location ?? '—',
                'unit_number'      => $b->unit?->unit_number ?? '—',
                'type_label'       => $b->unit?->type?->label() ?? '—',
                'bedrooms'         => $b->unit?->bedrooms ?? 0,
                'bathrooms'        => $b->unit?->bathrooms ?? 0,
                'size_sqft'        => $b->unit?->size_sqft ?? 0,
                'floor'            => $b->unit?->floor,
                'block'            => $b->unit?->block,
                'price_agreed'     => (float) $b->price_agreed,
                'status'           => $b->status->value,
                'status_label'     => $b->status->label(),
                'status_color'     => $b->status->color(),
                'cover_image'      => $b->unit?->getFirstMediaUrl('images')
                                      ?: $b->unit?->project?->getFirstMediaUrl('cover')
                                      ?: $b->unit?->project?->getFirstMediaUrl('images')
                                      ?: null,
                'eligible_banks'   => $b->unit?->eligible_banks ?? [],
                'max_loan_amount'  => (float) ($b->unit?->max_loan_amount ?? 0),
            ])->values();

        return Inertia::render('Client/Mortgage/Index', [
            'bookings' => $bookings,
        ]);
    }

    public function explore(Request $request): Response
    {
        $raw = (int) $request->query('price', 15000000);

        return Inertia::render('Client/Mortgage/Show', [
            'property'     => null,
            'isDemo'       => true,
            'defaultPrice' => max(1000000, min(80000000, $raw)),
        ]);
    }

    public function show(Booking $booking): Response
    {
        $client = Auth::guard('client')->user();
        abort_if($booking->client_id !== $client->id, 403);

        $booking->load(['unit.project.media', 'unit.media']);

        $property = [
            'id'               => $booking->id,
            'project_name'     => $booking->unit?->project?->name ?? '—',
            'project_location' => $booking->unit?->project?->location ?? '—',
            'unit_number'      => $booking->unit?->unit_number ?? '—',
            'type_label'       => $booking->unit?->type?->label() ?? '—',
            'bedrooms'         => $booking->unit?->bedrooms ?? 0,
            'bathrooms'        => $booking->unit?->bathrooms ?? 0,
            'size_sqft'        => $booking->unit?->size_sqft ?? 0,
            'floor'            => $booking->unit?->floor,
            'block'            => $booking->unit?->block,
            'price_agreed'     => (float) $booking->price_agreed,
            'status'           => $booking->status->value,
            'status_label'     => $booking->status->label(),
            'status_color'     => $booking->status->color(),
            'cover_image'      => $booking->unit?->getFirstMediaUrl('images')
                                  ?: $booking->unit?->project?->getFirstMediaUrl('cover')
                                  ?: $booking->unit?->project?->getFirstMediaUrl('images')
                                  ?: null,
            'eligible_banks'   => $booking->unit?->eligible_banks ?? [],
            'max_loan_amount'  => (float) ($booking->unit?->max_loan_amount ?? 0),
        ];

        return Inertia::render('Client/Mortgage/Show', [
            'property'     => $property,
            'isDemo'       => false,
            'defaultPrice' => null,
        ]);
    }
}
