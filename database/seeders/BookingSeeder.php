<?php

namespace Database\Seeders;

use App\Enums\UnitStatus;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Installment;
use App\Models\Payment;
use App\Models\PaymentPlan;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

// Seeds a fixed, controlled set of 30 bookings — enough status/date variety
// for the Index page's KPIs, trend chart, and pipeline to show real numbers
// without depending on however many units another seeder happened to flag
// Booked/Sold.
class BookingSeeder extends Seeder
{
    private array $sources    = ['Website', 'Walk-in', 'Referral', 'Facebook', 'Sales Call'];
    private array $priorities = ['Normal', 'Normal', 'Normal', 'High', 'VIP'];
    private array $planTypes  = ['24-month installment', '36-month installment', 'Custom Plan', 'Full Payment'];
    private array $banks      = ['City Bank', 'BRAC Bank', 'Eastern Bank'];
    private array $methods    = ['bank_transfer', 'bkash', 'nagad', 'card'];

    private const TOTAL     = 30;
    private const RESERVED  = 14;
    private const PURCHASED = 12;
    private const CANCELLED = 4; // TOTAL - RESERVED - PURCHASED

    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'homeverse'],
            ['name' => 'HomeVerse Real Estate', 'status' => 'active']
        );

        $reps = collect(['Fahim Ahmed', 'Jannatul Islam', 'Rasel Hossain', 'Nusrat Jahan'])
            ->map(function (string $name) use ($tenant) {
                $user = User::firstOrCreate(
                    ['email' => Str::slug($name).'@homeverse.com'],
                    [
                        'name'               => $name,
                        'password'           => bcrypt('password'),
                        'tenant_id'          => $tenant->id,
                        'email_verified_at'  => now(),
                    ]
                );
                if (! $user->hasRole('sales_manager')) {
                    $user->assignRole('sales_manager');
                }
                return $user;
            });

        $clients = Client::all();
        if ($clients->isEmpty()) {
            return;
        }

        // Wipe any previously seeded bookings (and, via FK cascade, their
        // payment plans / installments / payments) and free up the units
        // they held, so this run always produces exactly TOTAL bookings.
        $previouslyHeldUnitIds = Booking::pluck('unit_id')->filter()->all();
        Booking::query()->delete();
        Unit::whereIn('id', $previouslyHeldUnitIds)->update(['status' => UnitStatus::Available]);

        $units = Unit::where('status', UnitStatus::Available)
            ->inRandomOrder()
            ->take(self::TOTAL)
            ->get();

        if ($units->count() < self::TOTAL) {
            $this->command?->warn('Not enough available units to seed '.self::TOTAL.' bookings — seeding '.$units->count().' instead.');
        }

        $plan = collect(
            array_merge(
                array_fill(0, self::RESERVED, 'reserved'),
                array_fill(0, self::PURCHASED, 'purchased'),
                array_fill(0, self::CANCELLED, 'cancelled'),
            )
        )->shuffle();

        $units->values()->each(fn (Unit $unit, int $i) => $this->makeBooking($tenant, $unit, $plan[$i] ?? 'reserved', $clients, $reps));
    }

    private function makeBooking(Tenant $tenant, Unit $unit, string $status, $clients, $reps): void
    {
        $basePrice = (float) ($unit->current_price ?: $unit->price ?: $unit->launch_price ?: 5_000_000);
        $discount  = collect([0, 0, 2, 4, 5, 7.5])->random();
        $final     = round($basePrice * (1 - $discount / 100), 2);

        // Spread across ~6 months so the trend chart and month-over-month
        // KPI comparisons on the Index page have real variety to show.
        $bookingDate = now()->subDays(fake()->numberBetween(1, 180));

        $meta = [
            'mortgage' => [
                'loan_required' => fake()->boolean(60) ? 'Yes' : 'No',
                'eligible_bank' => collect($this->banks)->random(),
                'loan_amount'   => round($final * 0.8),
                'interest_rate' => 9.5,
                'status'        => $status === 'purchased' ? 'Approved' : 'Pre-approved',
            ],
            'approvals' => [
                'sales'   => 'Approved',
                'finance' => $status === 'cancelled' ? 'N/A' : 'Approved',
                'manager' => $status === 'purchased' ? 'Approved' : 'Pending',
                'legal'   => $status === 'purchased' ? 'Approved' : 'Not started',
            ],
            'agreement' => [
                'number' => 'AGR-'.now()->year.'-'.str_pad((string) fake()->numberBetween(1, 999), 4, '0', STR_PAD_LEFT),
                'status' => $status === 'purchased' ? 'Signed' : 'Draft',
            ],
        ];

        if ($status === 'cancelled') {
            $meta['cancellation'] = [
                'reason'       => collect(['Buyer financing fell through', 'Buyer chose another unit', 'Price renegotiation failed'])->random(),
                'cancelled_at' => $bookingDate->copy()->addDays(fake()->numberBetween(5, 20))->toDateString(),
            ];
        }

        $booking = Booking::create([
            'tenant_id'      => $tenant->id,
            'client_id'      => $clients->random()->id,
            'unit_id'        => $unit->id,
            'sales_rep_id'   => $reps->random()->id,
            'status'         => $status,
            'source'         => collect($this->sources)->random(),
            'priority'       => collect($this->priorities)->random(),
            'price_agreed'   => $final,
            'discount_pct'   => $discount,
            'booking_date'   => $bookingDate,
            'reserved_until' => $status === 'reserved' ? $bookingDate->copy()->addDays(30) : null,
            'notes'          => null,
            'meta'           => $meta,
        ]);

        // Keep the unit's own status consistent with the booking we just made.
        $unit->update(['status' => match ($status) {
            'reserved'  => UnitStatus::Booked,
            'purchased' => UnitStatus::Sold,
            default     => UnitStatus::Available, // cancelled — back on the market
        }]);

        if ($status === 'cancelled') {
            return;
        }

        $this->makePaymentPlan($tenant, $booking, $final, $status, $bookingDate);
    }

    private function makePaymentPlan(Tenant $tenant, Booking $booking, float $final, string $status, $bookingDate): void
    {
        $downPayment  = round($final * 0.2, 2);
        $installments = $status === 'purchased' ? collect([12, 18, 24])->random() : collect([24, 36])->random();
        $perInstallment = round(($final - $downPayment) / $installments, 2);

        $plan = PaymentPlan::create([
            'tenant_id'           => $tenant->id,
            'booking_id'          => $booking->id,
            'plan_type'           => collect($this->planTypes)->random(),
            'total_amount'        => $final,
            'down_payment'        => $downPayment,
            'start_date'          => $bookingDate,
            'total_installments'  => $installments,
            'duration_months'     => $installments,
            'status'              => $status === 'purchased' && fake()->boolean(40) ? 'completed' : 'active',
        ]);

        // The down payment itself is recorded as an ad-hoc payment (no installment row).
        Payment::create([
            'tenant_id'   => $tenant->id,
            'booking_id'  => $booking->id,
            'amount'      => $downPayment,
            'method'      => collect($this->methods)->random(),
            'reference'   => 'TXN-'.strtoupper(Str::random(8)),
            'status'      => 'completed',
            'paid_at'     => $bookingDate,
        ]);

        $paidCount = $status === 'purchased'
            ? fake()->numberBetween((int) ceil($installments / 2), $installments)
            : fake()->numberBetween(1, min(3, $installments));

        $milestones = ['Booking', 'Under Construction', 'Interior Work', 'Handover'];

        for ($i = 1; $i <= $installments; $i++) {
            $dueDate = $bookingDate->copy()->addMonths($i);
            $isPaid  = $i <= $paidCount;

            $installment = Installment::create([
                'tenant_id'           => $tenant->id,
                'payment_plan_id'     => $plan->id,
                'installment_number'  => $i,
                'milestone'           => $milestones[($i - 1) % count($milestones)],
                'description'         => 'Monthly Installment',
                'amount'              => $perInstallment,
                'due_date'            => $dueDate,
                'status'              => $isPaid ? 'paid' : ($dueDate->isPast() ? 'overdue' : 'upcoming'),
                'paid_at'             => $isPaid ? $dueDate : null,
            ]);

            if ($isPaid) {
                Payment::create([
                    'tenant_id'      => $tenant->id,
                    'booking_id'     => $booking->id,
                    'installment_id' => $installment->id,
                    'amount'         => $perInstallment,
                    'method'         => collect($this->methods)->random(),
                    'reference'      => 'TXN-'.strtoupper(Str::random(8)),
                    'status'         => 'completed',
                    'paid_at'        => $dueDate,
                ]);
            }
        }
    }
}
