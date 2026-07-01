<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    protected $fillable = [
        'tenant_id',
        'client_id',
        'unit_id',
        'sales_rep_id',
        'status',
        'source',
        'priority',
        'price_agreed',
        'discount_pct',
        'booking_date',
        'reserved_until',
        'handover_date',
        'notes',
        'meta',
    ];

    protected $casts = [
        'booking_date'   => 'date',
        'reserved_until' => 'date',
        'handover_date'  => 'date',
        'price_agreed'   => 'decimal:2',
        'discount_pct'   => 'decimal:2',
        'meta'           => 'array',
        'status'         => BookingStatus::class,
    ];

    // ── Relationships ──────────────────────────────────────────────

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function salesRep(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_rep_id');
    }

    public function paymentPlan(): HasOne
    {
        return $this->hasOne(PaymentPlan::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
