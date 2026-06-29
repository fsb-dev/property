<?php

namespace App\Models;

use App\Enums\UnitStatus;
use App\Enums\UnitType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Unit extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        // Identity
        'tenant_id', 'project_id', 'block_id', 'floor', 'floor_end', 'sort_order',
        'unit_number', 'unit_code', 'type', 'wing', 'block', 'description',

        // Specifications
        'bedrooms', 'bathrooms', 'balconies', 'servant_room', 'store_room',
        'parking_spaces', 'facing_direction', 'view',

        // Measurements
        'size_sqft', 'super_built_up_area', 'carpet_area',
        'ceiling_height', 'terrace_area', 'parking_area',

        // Pricing
        'price', 'launch_price', 'current_price', 'parking_price',
        'registration_fee', 'vat_pct', 'monthly_maintenance', 'booking_amount',

        // Media URLs
        'video_url', 'tour_360_url',

        // Availability
        'status', 'launch_date', 'available_date', 'handover_date',

        // Mortgage
        'eligible_banks', 'max_loan_amount', 'payment_plan_months',
    ];

    protected $casts = [
        'servant_room'   => 'boolean',
        'store_room'     => 'boolean',
        'eligible_banks' => 'array',
        'launch_date'    => 'date',
        'available_date' => 'date',
        'handover_date'  => 'date',
        'type'           => UnitType::class,
        'status'         => UnitStatus::class,
    ];

    public function isConfigured(): bool
    {
        return $this->status !== UnitStatus::NotConfigured;
    }

    // ── Media collections ──────────────────────────────────────────

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('drone')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('interior')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('floor_plan')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('floor_plan_pdf')
            ->singleFile()
            ->acceptsMimeTypes(['application/pdf']);

        $this->addMediaCollection('cad_dwg')
            ->singleFile()
            ->acceptsMimeTypes(['application/octet-stream', 'application/acad',
                'image/vnd.dwg', 'application/dxf']);

        $this->addMediaCollection('documents')
            ->acceptsMimeTypes(['application/pdf', 'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);
    }

    // ── Relationships ──────────────────────────────────────────────

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(ProjectBlock::class, 'block_id');
    }
}
