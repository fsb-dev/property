<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BookingRequest\SaveBookingDraftRequest;
use App\Http\Requests\Admin\BookingRequest\StoreBookingRequest;
use App\Http\Requests\Admin\BookingRequest\UpdateBookingRequest;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(private BookingService $service) {}

    public function index(Request $request)
    {
        return inertia('Admin/Bookings/Index', [
            'bookings' => $this->service->paginate($request),
            'stats'    => $this->service->stats(),
            'enums'    => $this->service->enums(),
            'filters'  => $request->only('search', 'status'),
        ]);
    }

    public function create()
    {
        return inertia('Admin/Bookings/Create', [
            'enums' => $this->service->enums(),
        ]);
    }

    public function store(StoreBookingRequest $request)
    {
        $booking = $this->service->create($request->validated());

        return redirect()->route('admin.bookings.show', $booking)
            ->with('success', 'Reservation created successfully.');
    }

    public function saveDraft(SaveBookingDraftRequest $request)
    {
        $booking = $this->service->saveDraft($request->validated());

        return response()->json([
            'id'       => $booking->id,
            'saved_at' => $booking->updated_at,
        ]);
    }

    public function show(Booking $booking)
    {
        return inertia('Admin/Bookings/Show', [
            'booking' => $this->service->forShow($booking),
        ]);
    }

    public function edit(Booking $booking)
    {
        return inertia('Admin/Bookings/Edit', [
            'booking' => $this->service->forEdit($booking),
            'enums'   => $this->service->enums(),
        ]);
    }

    public function update(UpdateBookingRequest $request, Booking $booking)
    {
        $this->service->update($booking, $request->validated());

        return redirect()->route('admin.bookings.show', $booking)
            ->with('success', 'Reservation updated successfully.');
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $request->validate([
            'status' => ['required', 'in:reserved,purchased,cancelled,handed_over'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $this->service->updateStatus($booking, $request->status, $request->reason);

        return back()->with('success', 'Reservation status updated.');
    }

    public function destroy(Booking $booking)
    {
        $this->service->delete($booking);

        return redirect()->route('admin.bookings.index')
            ->with('success', 'Reservation removed.');
    }
}
