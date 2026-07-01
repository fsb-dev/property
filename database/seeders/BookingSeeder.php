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

class BookingSeeder extends Seeder
{
    private array $sources    = ['Website', 'Walk-in', 'Referral', 'Facebook', 'Sales Call'];
    private array $priorities = ['Normal', 'Normal', 'Normal', 'High', 'VIP'];
    private array $planTypes  = ['24-month installment', '36-month installment', 'Custom Plan', 'Full Payment'];
    private array $banks      = ['City Bank', 'BRAC Bank', 'Eastern Bank'];
    private array $methods    = ['bank_transfer', 'bkash', 'nagad', 'card'];

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

        // Real units already sitting in Booked/Sold status (ProjectSeeder's status cycle) —
        // one Booking each, so unit status and booking status agree.
        Unit::where('status', UnitStatus::Booked)->get()
            ->each(fn (Unit $unit) => $this->makeBooking($tenant, $unit, 'reserved', $clients, $reps));

        Unit::where('status', UnitStatus::Sold)->get()
            ->each(fn (Unit $unit) => $this->makeBooking($tenant, $unit, 'purchased', $clients, $reps));

        // A handful of historical cancellations on units that are Available again.
        Unit::where('status', UnitStatus::Available)->inRandomOrder()->take(4)->get()
            ->each(fn (Unit $unit) => $this->makeBooking($tenant, $unit, 'cancelled', $clients, $reps));
    }

    private function makeBooking(Tenant $tenant, Unit $unit, string $status, $clients, $reps): void
    {
        $basePrice = (float) ($unit->current_price ?: $unit->price ?: $unit->launch_price ?: 5_000_000);
        $discount  = collect([0, 0, 2, 4, 5, 7.5])->random();
        $final     = round($basePrice * (1 - $discount / 100), 2);

        $bookingDate = now()->subDays(fake()->numberBetween(5, 150));

        $meta = [
            'documents' => [
                'national_id'      => 'Verified',
                'income_proof'     => 'Uploaded',
                'bank_statement'   => 'Uploaded',
                'agreement_draft'  => $status === 'purchased' ? 'Signed' : 'Pending',
            ],
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
