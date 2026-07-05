<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Installment;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PaymentsController extends Controller
{
    public function index(): Response
    {
        $client = Auth::guard('client')->user();

        // All bookings with plans + installments + payments
        $bookings = $client->bookings()
            ->with([
                'unit.project',
                'paymentPlan.installments',
                'payments' => fn ($q) => $q->with('installment')->whereNotNull('paid_at')->latest('paid_at'),
            ])
            ->latest('booking_date')
            ->get();

        // ── KPI aggregates ────────────────────────────────────────
        $totalValue = (float) $bookings->sum('price_agreed');

        $totalPaid = (float) Payment::whereHas(
            'booking', fn ($q) => $q->where('client_id', $client->id)
        )->whereNotNull('paid_at')->sum('amount');

        $outstanding = $totalValue - $totalPaid;
        $paidPct     = $totalValue > 0 ? round(($totalPaid / $totalValue) * 100) : 0;

        $totalInstallments = (int) $bookings->sum(
            fn ($b) => $b->paymentPlan?->total_installments ?? 0
        );
        $paidInstallments = (int) $bookings->sum(
            fn ($b) => $b->paymentPlan?->installments?->filter(fn ($i) => $i->paid_at !== null)->count() ?? 0
        );

        // ── Per-booking list for property selector ────────────────
        $bookingsList = $bookings->map(fn ($b) => [
            'id'                 => $b->id,
            'project_name'       => $b->unit?->project?->name ?? '—',
            'unit_number'        => $b->unit?->unit_number ?? '—',
            'price_agreed'       => (float) $b->price_agreed,
            'total_paid'         => (float) $b->payments->sum('amount'),
            'plan_type'          => $b->paymentPlan?->plan_type ?? null,
            'start_date'         => $b->paymentPlan?->start_date?->format('d M Y')
                                    ?? $b->booking_date?->format('d M Y')
                                    ?? '—',
            'handover_date'      => $b->paymentPlan?->handover_date?->format('M Y')
                                    ?? $b->unit?->project?->handover_date?->format('M Y')
                                    ?? '—',
            'duration_months'    => $b->paymentPlan?->duration_months,
            'total_installments' => $b->paymentPlan?->total_installments ?? 0,
            'paid_installments'  => $b->paymentPlan?->installments?->filter(fn ($i) => $i->paid_at !== null)->count() ?? 0,
            'booking_date'       => $b->booking_date?->format('d M Y') ?? '—',
        ])->values();

        // ── Recent payments (last 20 across all bookings) ─────────
        $recentPayments = Payment::whereHas(
            'booking', fn ($q) => $q->where('client_id', $client->id)
        )->with(['installment', 'booking.unit.project'])
            ->whereNotNull('paid_at')
            ->latest('paid_at')
            ->take(20)
            ->get()
            ->map(fn ($p) => [
                'id'             => $p->id,
                'booking_id'     => $p->booking_id,
                'date'           => $p->paid_at->format('d M Y'),
                'installment_no' => $p->installment?->installment_number,
                'milestone'      => $p->installment?->milestone,
                'description'    => $p->installment?->description ?? ($p->notes ?? 'Payment'),
                'amount'         => (float) $p->amount,
                'method'         => $p->method ?? '—',
                'project_name'   => $p->booking?->unit?->project?->name ?? '—',
                'unit_number'    => $p->booking?->unit?->unit_number ?? '—',
                'receipt_path'   => $p->receipt_path,
            ])->values();

        // ── Next unpaid installment (across all plans) ────────────
        $next = Installment::whereHas(
            'paymentPlan.booking', fn ($q) => $q->where('client_id', $client->id)
        )->whereNull('paid_at')
            ->where('status', '!=', 'cancelled')
            ->orderBy('due_date')
            ->with(['paymentPlan.booking.unit.project'])
            ->first();

        $upcomingPayment = null;
        if ($next) {
            $dueDate = $next->due_date;
            $daysRem = $dueDate ? (int) now()->diffInDays($dueDate, false) : null;
            $upcomingPayment = [
                'installment_number' => $next->installment_number,
                'due_date'           => $dueDate?->format('d M Y') ?? '—',
                'amount'             => (float) $next->amount,
                'days_remaining'     => $daysRem,
                'is_overdue'         => $daysRem !== null && $daysRem < 0,
                'project_name'       => $next->paymentPlan?->booking?->unit?->project?->name ?? '—',
                'unit_number'        => $next->paymentPlan?->booking?->unit?->unit_number ?? '—',
                'milestone'          => $next->milestone,
                'booking_id'         => $next->paymentPlan?->booking_id,
            ];
        }

        return Inertia::render('Client/Payments', [
            'bookingsList'       => $bookingsList,
            'totalValue'         => $totalValue,
            'totalPaid'          => $totalPaid,
            'outstanding'        => $outstanding,
            'paidPct'            => $paidPct,
            'totalInstallments'  => $totalInstallments,
            'paidInstallments'   => $paidInstallments,
            'recentPayments'     => $recentPayments,
            'upcomingPayment'    => $upcomingPayment,
        ]);
    }
}
