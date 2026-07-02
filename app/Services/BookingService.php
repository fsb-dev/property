<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\UnitStatus;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Installment;
use App\Models\PaymentPlan;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ProjectBuilding;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingService
{
    private array $sources    = ['Website', 'Walk-in', 'Referral', 'Facebook', 'Sales Call'];
    private array $priorities = ['Normal', 'High', 'VIP'];
    private array $planTypes  = ['24-month installment', '36-month installment', 'Custom Plan', 'Full Payment'];

    // ── Index ────────────────────────────────────────────────────────────

    public function paginate(Request $request): LengthAwarePaginator
    {
        return Booking::query()
            ->with(['client:id,name,phone', 'unit:id,project_id,unit_number', 'unit.project:id,name', 'salesRep:id,name'])
            ->when($request->search, fn ($q) => $q->where(function ($q) use ($request) {
                $term = "%{$request->search}%";
                $q->whereHas('client', fn ($c) => $c->where('name', 'like', $term)->orWhere('phone', 'like', $term))
                    ->orWhereHas('unit', fn ($u) => $u->where('unit_number', 'like', $term))
                    ->orWhereHas('unit.project', fn ($p) => $p->where('name', 'like', $term));
            }))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest('booking_date')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Booking $b) => [
                'id'           => $b->id,
                'code'         => $this->code($b),
                'date'         => $b->booking_date?->format('d M Y'),
                'type'         => $b->status === BookingStatus::Purchased ? 'Sale' : ($b->status === BookingStatus::Cancelled ? 'Cancelled' : 'Reservation'),
                'buyer'        => $b->client?->name,
                'phone'        => $b->client?->phone,
                'project'      => $b->unit?->project?->name,
                'unit'         => $b->unit?->unit_number,
                'amount'       => (float) $b->price_agreed,
                'rep'          => $b->salesRep?->name,
                'status'       => $b->status->value,
                'status_label' => $b->status->label(),
                'status_color' => $b->status->color(),
                'action'       => match ($b->status) {
                    BookingStatus::Reserved  => 'Payment Due',
                    BookingStatus::Purchased => 'Handover Prep',
                    BookingStatus::Cancelled => 'Cancelled',
                    default => '—',
                },
                'action_sub'   => match ($b->status) {
                    BookingStatus::Reserved  => $b->reserved_until ? 'Expires '.$b->reserved_until->format('d M') : null,
                    BookingStatus::Cancelled => $b->meta['cancellation']['reason'] ?? null,
                    default => null,
                },
            ]);
    }

    public function stats(): array
    {
        $reserved  = Booking::where('status', BookingStatus::Reserved)->count();
        $purchased = Booking::where('status', BookingStatus::Purchased)->count();
        $cancelled = Booking::where('status', BookingStatus::Cancelled)->count();
        $salesValue = (float) Booking::where('status', BookingStatus::Purchased)->sum('price_agreed');
        $total = $reserved + $purchased + $cancelled;

        return [
            'total_reservations' => $reserved,
            'total_sales'        => $purchased,
            'sales_value'        => $salesValue,
            'conversion_rate'    => $total > 0 ? round($purchased / $total * 100, 1) : 0,
            'cancelled'          => $cancelled,
            'all_count'          => $total,
            'trend'              => $this->salesTrend(),
            'by_project'         => $this->salesByProject(),
            'top_reps'           => $this->topReps(),
        ];
    }

    private function salesTrend(): array
    {
        $months = collect(range(5, 0))->map(fn ($i) => now()->copy()->subMonths($i)->startOfMonth());

        return $months->map(function ($month) {
            $value = Booking::where('status', BookingStatus::Purchased)
                ->whereBetween('booking_date', [$month, $month->copy()->endOfMonth()])
                ->sum('price_agreed');

            return ['label' => $month->format('M Y'), 'value' => (float) $value];
        })->values()->all();
    }

    private function salesByProject(): array
    {
        $rows = Booking::where('status', BookingStatus::Purchased)
            ->with('unit.project:id,name')
            ->get()
            ->groupBy(fn (Booking $b) => $b->unit?->project?->name ?? 'Unassigned')
            ->map(fn ($group) => (float) $group->sum('price_agreed'))
            ->sortDesc();

        $total = $rows->sum();
        $colors = ['#3B82F6', '#22C55E', '#EF4444', '#F59E0B', '#5B3DF5'];

        $top = $rows->take(5);
        $others = $rows->slice(5)->sum();

        $result = $top->map(function ($value, $name) use ($total, &$colors) {
            static $i = 0;
            return [
                'name'    => $name,
                'value'   => $value,
                'percent' => $total > 0 ? round($value / $total * 100) : 0,
                'color'   => $colors[$i++] ?? '#C7CDDA',
            ];
        })->values()->all();

        if ($others > 0) {
            $result[] = ['name' => 'Others', 'value' => $others, 'percent' => $total > 0 ? round($others / $total * 100) : 0, 'color' => '#C7CDDA'];
        }

        return $result;
    }

    private function topReps(): array
    {
        return Booking::where('status', BookingStatus::Purchased)
            ->whereNotNull('sales_rep_id')
            ->with('salesRep:id,name')
            ->get()
            ->groupBy('sales_rep_id')
            ->map(fn ($group) => [
                'name'   => $group->first()->salesRep?->name ?? 'Unassigned',
                'deals'  => $group->count(),
                'revenue'=> (float) $group->sum('price_agreed'),
            ])
            ->sortByDesc('revenue')
            ->take(4)
            ->values()
            ->all();
    }

    public function enums(): array
    {
        return [
            'statuses' => collect(BookingStatus::cases())->map(fn ($s) => [
                'value' => $s->value, 'label' => $s->label(), 'color' => $s->color(),
            ]),
            'sources'   => $this->sources,
            'priorities' => $this->priorities,
            'plan_types' => $this->planTypes,
            'projects' => Project::query()->orderBy('name')->get(['id', 'name'])->map(fn ($p) => [
                'id' => $p->id, 'name' => $p->name,
            ]),
            'buildings' => ProjectBuilding::query()->orderBy('name')->get(['id', 'project_id', 'name'])->map(fn ($b) => [
                'id' => $b->id, 'project_id' => $b->project_id, 'name' => $b->name,
            ]),
            'clients' => Client::query()->orderBy('name')->get(['id', 'name', 'phone']),
            'sales_reps' => User::role('sales_manager')->get(['id', 'name']),
        ];
    }

    // ── Show / Edit ──────────────────────────────────────────────────────

    public function forShow(Booking $booking): array
    {
        $booking->load(['client', 'unit.project', 'salesRep', 'paymentPlan.installments', 'payments']);

        $unit  = $booking->unit;
        $plan  = $booking->paymentPlan;
        $meta  = $booking->meta ?? [];

        $collected = (float) $booking->payments->sum('amount');
        $total     = (float) $booking->price_agreed;

        $basePrice = (float) ($unit?->current_price ?: $unit?->price ?: $total);
        $discountAmount = round($basePrice * (float) ($booking->discount_pct ?? 0) / 100, 2);

        $nextInstallment = $plan?->installments->firstWhere('status', 'upcoming');
        $nextDue = $nextInstallment ? [
            'due_date' => $nextInstallment->due_date?->format('d M Y'),
            'amount'   => (float) $nextInstallment->amount,
        ] : null;

        return [
            'id'             => $booking->id,
            'code'           => $this->code($booking),
            'status'         => $booking->status->value,
            'status_label'   => $booking->status->label(),
            'status_color'   => $booking->status->color(),
            'booking_date'   => $booking->booking_date?->format('d M Y'),
            'reserved_until' => $booking->reserved_until?->format('d M Y'),
            'source'         => $booking->source,
            'priority'       => $booking->priority,
            'notes'          => $booking->notes,

            'buyer' => $booking->client ? [
                'id'    => $booking->client->id,
                'name'  => $booking->client->name,
                'phone' => $booking->client->phone,
                'email' => $booking->client->email,
                'nid'   => $booking->client->nid,
            ] : null,

            'project' => $unit?->project ? [
                'id'   => $unit->project->id,
                'name' => $unit->project->name,
                'location' => $unit->project->location,
                'handover' => $unit->project->handover_date?->format('M Y'),
            ] : null,

            'unit' => $unit ? [
                'id'          => $unit->id,
                'unit_number' => $unit->unit_number,
                'type'        => $unit->type?->label(),
                'floor'       => $unit->floor,
                'bedrooms'    => $unit->bedrooms,
                'bathrooms'   => $unit->bathrooms,
                'size_sqft'   => $unit->size_sqft,
                'status'      => $unit->status->label(),
            ] : null,

            'sales_rep' => $booking->salesRep ? [
                'id'    => $booking->salesRep->id,
                'name'  => $booking->salesRep->name,
                'deals' => Booking::where('sales_rep_id', $booking->salesRep->id)->count(),
            ] : null,

            'pricing' => [
                'base_price'      => $basePrice,
                'discount_pct'    => (float) ($booking->discount_pct ?? 0),
                'discount_amount' => $discountAmount,
                'final_price'     => $total,
            ],

            'payment_plan' => $plan ? [
                'plan_type'    => $plan->plan_type,
                'down_payment' => (float) $plan->down_payment,
                'total_installments' => $plan->total_installments,
                'status'       => $plan->status,
                'installments' => $plan->installments->map(fn ($i) => [
                    'number'   => $i->installment_number,
                    'milestone'=> $i->milestone,
                    'amount'   => (float) $i->amount,
                    'due_date' => $i->due_date?->format('d M Y'),
                    'status'   => $i->status,
                ]),
            ] : null,

            'payments' => [
                'collected'    => $collected,
                'total'        => $total,
                'percent'      => $total > 0 ? round($collected / $total * 100) : 0,
                'next_due'     => $nextDue,
            ],

            'meta' => [
                'mortgage'     => $meta['mortgage'] ?? null,
                'approvals'    => $meta['approvals'] ?? null,
                'agreement'    => $meta['agreement'] ?? null,
                'cancellation' => $meta['cancellation'] ?? null,
            ],

            'timeline' => [
                ['label' => 'Reserved', 'date' => $booking->booking_date?->format('d M Y')],
                ['label' => 'Down Payment', 'date' => $booking->payments->sortBy('paid_at')->first()?->paid_at?->format('d M Y')],
                ['label' => 'Expected Handover', 'date' => $unit?->handover_date?->format('M Y') ?? $unit?->project?->handover_date?->format('M Y')],
            ],

            'activity' => $booking->payments->sortByDesc('paid_at')->take(6)->values()->map(fn ($p) => [
                'label' => ($p->installment_id ? 'Installment payment received' : 'Down payment received').' · '.number_format((float) $p->amount),
                'date'  => $p->paid_at?->format('d M Y'),
            ]),

            'created_at' => $booking->created_at->format('d M Y, h:i A'),
        ];
    }

    public function forEdit(Booking $booking): array
    {
        $booking->load('paymentPlan');
        $plan = $booking->paymentPlan;
        $meta = $booking->meta ?? [];

        return [
            'id' => $booking->id,

            // ── Buyer — an existing booking always has a real client already ──
            'buyer_mode'       => 'existing',
            'client_id'        => $booking->client_id,
            'new_client_name'  => '',
            'new_client_phone' => '',
            'new_client_email' => '',

            // ── Unit ──────────────────────────────────────────────────────
            'unit_id' => $booking->unit_id,

            // ── Reservation details ─────────────────────────────────────
            'booking_date'   => $booking->booking_date?->format('Y-m-d'),
            'reserved_until' => $booking->reserved_until?->format('Y-m-d'),
            'source'         => $booking->source,
            'priority'       => $booking->priority ?? 'Normal',
            'sales_rep_id'   => $booking->sales_rep_id,
            'notes'          => $booking->notes,

            // ── Pricing ───────────────────────────────────────────────────
            'discount_pct' => (float) ($booking->discount_pct ?? 0),
            'price_agreed' => (float) $booking->price_agreed,

            // ── Payment plan ──────────────────────────────────────────────
            'plan_type'          => $plan?->plan_type ?? '24-month installment',
            'down_payment'       => $plan ? (float) $plan->down_payment : '',
            'total_installments' => $plan?->total_installments ?? 24,

            // ── Demo-only extras (mortgage / approvals) ────────────────────
            'meta' => [
                'mortgage'  => $meta['mortgage'] ?? ['loan_required' => 'No', 'eligible_bank' => '', 'loan_amount' => '', 'interest_rate' => '', 'indicative_emi' => '', 'status' => ''],
                'approvals' => $meta['approvals'] ?? ['sales' => 'Pending', 'finance' => 'Pending', 'manager' => 'Pending', 'legal' => 'Pending'],
            ],

            // ── Read-only extras for the page header ─────────────────────
            'status'       => $booking->status->value,
            'status_label' => $booking->status->label(),
            'buyer_name'   => $booking->client?->name,
            'unit_number'  => $booking->unit?->unit_number,
        ];
    }

    // ── Mutations ────────────────────────────────────────────────────────

    public function create(array $data): Booking
    {
        return DB::transaction(function () use ($data) {
            $tenant = Tenant::first() ?? Tenant::create(['name' => 'HomeVerse Real Estate', 'slug' => 'homeverse', 'status' => 'active']);

            $clientId = $data['buyer_mode'] === 'new'
                ? Client::create([
                    'tenant_id' => $tenant->id,
                    'name'      => $data['new_client_name'],
                    'phone'     => $data['new_client_phone'] ?? null,
                    'email'     => $data['new_client_email'] ?? null,
                    'password'  => Str::random(12),
                    'status'    => 'active',
                ])->id
                : $data['client_id'];

            $unit = Unit::findOrFail($data['unit_id']);

            $attributes = [
                'tenant_id'      => $tenant->id,
                'client_id'      => $clientId,
                'unit_id'        => $unit->id,
                'sales_rep_id'   => $data['sales_rep_id'] ?? null,
                'status'         => BookingStatus::Reserved,
                'source'         => $data['source'] ?? null,
                'priority'       => $data['priority'] ?? 'Normal',
                'price_agreed'   => $data['price_agreed'],
                'discount_pct'   => $data['discount_pct'] ?? 0,
                'booking_date'   => $data['booking_date'],
                'reserved_until' => $data['reserved_until'] ?? null,
                'notes'          => $data['notes'] ?? null,
                'meta'           => $data['meta'] ?? null,
            ];

            // publishing a saved draft — convert that row rather than leaving it orphaned
            $draft = !empty($data['draft_id'])
                ? Booking::where('id', $data['draft_id'])->where('status', BookingStatus::Draft)->first()
                : null;

            if ($draft) {
                $draft->update($attributes);
                $booking = $draft;
            } else {
                $booking = Booking::create($attributes);
            }

            $downPayment  = (float) ($data['down_payment'] ?? round($data['price_agreed'] * 0.2, 2));
            $installments = (int) ($data['total_installments'] ?? 24);

            $plan = PaymentPlan::create([
                'tenant_id'          => $tenant->id,
                'booking_id'         => $booking->id,
                'plan_type'          => $data['plan_type'] ?? '24-month installment',
                'total_amount'       => $data['price_agreed'],
                'down_payment'       => $downPayment,
                'start_date'         => $data['booking_date'],
                'total_installments' => $installments,
                'duration_months'    => $installments,
                'status'             => 'active',
            ]);

            $this->generateInstallments($tenant, $plan, $downPayment, $data['price_agreed'], $installments, $data['booking_date']);

            $unit->update(['status' => UnitStatus::Booked]);

            return $booking->fresh();
        });
    }

    // Saves the wizard's current state as a bookings row with status=draft.
    // Deliberately skips client creation, payment plan generation and unit
    // status changes — none of that is real until the reservation is published.
    public function saveDraft(array $data): Booking
    {
        $tenant = Tenant::first() ?? Tenant::create(['name' => 'HomeVerse Real Estate', 'slug' => 'homeverse', 'status' => 'active']);

        $attributes = [
            'tenant_id'      => $tenant->id,
            'client_id'      => ($data['buyer_mode'] ?? null) === 'existing' ? ($data['client_id'] ?? null) : null,
            'unit_id'        => $data['unit_id'] ?? null,
            'sales_rep_id'   => $data['sales_rep_id'] ?? null,
            'status'         => BookingStatus::Draft,
            'source'         => $data['source'] ?? null,
            'priority'       => $data['priority'] ?? 'Normal',
            'price_agreed'   => $data['price_agreed'] ?? 0,
            'discount_pct'   => $data['discount_pct'] ?? 0,
            'booking_date'   => $data['booking_date'] ?? null,
            'reserved_until' => $data['reserved_until'] ?? null,
            'notes'          => $data['notes'] ?? null,
            'meta'           => $data['meta'] ?? null,
        ];

        $booking = !empty($data['id'])
            ? Booking::where('id', $data['id'])->where('status', BookingStatus::Draft)->first()
            : null;

        if ($booking) {
            $booking->update($attributes);
        } else {
            $booking = Booking::create($attributes);
        }

        return $booking;
    }

    // Same shape as create(): buyer, unit, pricing and plan are all editable
    // through the wizard. `status` is deliberately untouched here — status
    // transitions stay on updateStatus().
    public function update(Booking $booking, array $data): Booking
    {
        return DB::transaction(function () use ($booking, $data) {
            $tenant = Tenant::find($booking->tenant_id) ?? Tenant::first();

            $clientId = $data['buyer_mode'] === 'new'
                ? Client::create([
                    'tenant_id' => $tenant->id,
                    'name'      => $data['new_client_name'],
                    'phone'     => $data['new_client_phone'] ?? null,
                    'email'     => $data['new_client_email'] ?? null,
                    'password'  => Str::random(12),
                    'status'    => 'active',
                ])->id
                : $data['client_id'];

            $oldUnitId = $booking->unit_id;
            $unit = Unit::findOrFail($data['unit_id']);

            $booking->update([
                'client_id'      => $clientId,
                'unit_id'        => $unit->id,
                'sales_rep_id'   => $data['sales_rep_id'] ?? null,
                'source'         => $data['source'] ?? null,
                'priority'       => $data['priority'] ?? $booking->priority,
                'price_agreed'   => $data['price_agreed'],
                'discount_pct'   => $data['discount_pct'] ?? 0,
                'booking_date'   => $data['booking_date'],
                'reserved_until' => $data['reserved_until'] ?? null,
                'notes'          => $data['notes'] ?? null,
                'meta'           => $data['meta'] ?? $booking->meta,
            ]);

            // the unit changed hands — free up whichever one we're leaving
            if ($oldUnitId && $oldUnitId !== $unit->id) {
                Unit::where('id', $oldUnitId)->where('status', UnitStatus::Booked)
                    ->update(['status' => UnitStatus::Available]);
            }

            $this->syncUnitStatus($booking->fresh());
            $this->reconcilePaymentPlan($booking->fresh(), $tenant, $data, $oldUnitId !== $unit->id);

            return $booking->fresh();
        });
    }

    // Only reshapes the installment schedule when nothing has actually been
    // collected against it yet. Every booking gets an auto-recorded down
    // payment at creation time, so we specifically check for installments
    // that have progressed past "upcoming" — not just any Payment row —
    // otherwise this guard would never fire and edits could never reshape
    // a plan.
    private function reconcilePaymentPlan(Booking $booking, Tenant $tenant, array $data, bool $unitChanged): void
    {
        $plan = $booking->paymentPlan;
        $hasCollectedInstallments = $plan && $plan->installments()->where('status', '!=', 'upcoming')->exists();

        $planChanged = !$plan
            || $unitChanged
            || (float) $plan->total_amount !== (float) $data['price_agreed']
            || (int) $plan->total_installments !== (int) ($data['total_installments'] ?? $plan->total_installments)
            || $plan->plan_type !== ($data['plan_type'] ?? $plan->plan_type);

        if (!$planChanged || $hasCollectedInstallments) {
            return;
        }

        $plan?->delete(); // cascades installments; any payments simply lose their installment_id

        $downPayment  = (float) ($data['down_payment'] ?? round($data['price_agreed'] * 0.2, 2));
        $installments = (int) ($data['total_installments'] ?? 24);

        $newPlan = PaymentPlan::create([
            'tenant_id'          => $tenant->id,
            'booking_id'         => $booking->id,
            'plan_type'          => $data['plan_type'] ?? '24-month installment',
            'total_amount'       => $data['price_agreed'],
            'down_payment'       => $downPayment,
            'start_date'         => $data['booking_date'],
            'total_installments' => $installments,
            'duration_months'    => $installments,
            'status'             => 'active',
        ]);

        $this->generateInstallments($tenant, $newPlan, $downPayment, $data['price_agreed'], $installments, $data['booking_date']);
    }

    public function updateStatus(Booking $booking, string $status, ?string $reason = null): Booking
    {
        $meta = $booking->meta ?? [];

        if ($status === BookingStatus::Cancelled->value) {
            $meta['cancellation'] = ['reason' => $reason, 'cancelled_at' => now()->toDateString()];
        }

        $booking->update(['status' => $status, 'meta' => $meta]);
        $this->syncUnitStatus($booking);

        return $booking->fresh();
    }

    public function delete(Booking $booking): void
    {
        $unit = $booking->unit;
        $booking->delete();

        if ($unit && $unit->status === UnitStatus::Booked) {
            $unit->update(['status' => UnitStatus::Available]);
        }
    }

    private function syncUnitStatus(Booking $booking): void
    {
        $unit = $booking->unit;
        if (! $unit) {
            return;
        }

        match ($booking->status) {
            BookingStatus::Purchased => $unit->update(['status' => UnitStatus::Sold]),
            BookingStatus::Cancelled => $unit->update(['status' => UnitStatus::Available]),
            BookingStatus::Reserved  => $unit->update(['status' => UnitStatus::Booked]),
            default => null,
        };
    }

    private function generateInstallments(Tenant $tenant, PaymentPlan $plan, float $downPayment, float $total, int $count, $startDate): void
    {
        Payment::create([
            'tenant_id'  => $tenant->id,
            'booking_id' => $plan->booking_id,
            'amount'     => $downPayment,
            'method'     => 'bank_transfer',
            'reference'  => 'TXN-'.strtoupper(Str::random(8)),
            'status'     => 'completed',
            'paid_at'    => $startDate,
        ]);

        $perInstallment = round(($total - $downPayment) / max($count, 1), 2);
        $milestones = ['Booking', 'Under Construction', 'Interior Work', 'Handover'];
        $start = \Illuminate\Support\Carbon::parse($startDate);

        for ($i = 1; $i <= $count; $i++) {
            Installment::create([
                'tenant_id'          => $tenant->id,
                'payment_plan_id'    => $plan->id,
                'installment_number' => $i,
                'milestone'          => $milestones[($i - 1) % count($milestones)],
                'description'        => 'Monthly Installment',
                'amount'             => $perInstallment,
                'due_date'           => $start->copy()->addMonths($i),
                'status'             => 'upcoming',
            ]);
        }
    }

    private function code(Booking $booking): string
    {
        return 'RSV-'.$booking->created_at->year.'-'.str_pad((string) $booking->id, 4, '0', STR_PAD_LEFT);
    }
}
