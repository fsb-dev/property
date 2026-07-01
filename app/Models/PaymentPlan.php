<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentPlan extends Model
{
    protected $fillable = [
        'tenant_id',
        'booking_id',
        'plan_type',
        'total_amount',
        'down_payment',
        'start_date',
        'handover_date',
        'total_installments',
        'duration_months',
        'status',
    ];

    protected $casts = [
        'start_date'     => 'date',
        'handover_date'  => 'date',
        'total_amount'   => 'decimal:2',
        'down_payment'   => 'decimal:2',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class)->orderBy('installment_number');
    }
}
