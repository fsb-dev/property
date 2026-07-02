<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use App\Enums\ProjectType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Project extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'tenant_id',
        'developer_id',
        'name',
        'slug',
        'project_code',
        'theme_color',
        'developer_name',
        'type',
        'status',
        'description',
        'location',
        'address',
        'latitude',
        'longitude',
        'start_date',
        'handover_date',
        'total_floors',
        'total_units',
        'overall_progress',
        'land_area',
        'land_area_unit',
        'built_up_area',
        'estimated_value',
        'booking_amount',
        'booking_amount_type',
        'commission_pct',
        'payment_plan_months',
        'service_charge_sqft',
        'maintenance_years',
        'specifications',
        'published_at',
    ];

    protected $casts = [
        'start_date'       => 'date',
        'handover_date'    => 'date',
        'published_at'     => 'datetime',
        'overall_progress' => 'decimal:2',
        'specifications'   => 'array',
        'type'             => ProjectType::class,
        'status'           => ProjectStatus::class,
    ];

    // ── Media collections ──────────────────────────────────────────

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('documents')
            ->acceptsMimeTypes([
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
    }

    // ── Relationships ──────────────────────────────────────────────

    public function developer(): BelongsTo
    {
        return $this->belongsTo(Developer::class);
    }

    public function buildings(): HasMany
    {
        return $this->hasMany(ProjectBuilding::class)->orderBy('sort_order');
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(ProjectBlock::class)->orderBy('sort_order');
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(Facility::class, 'project_facility');
    }

    public function compliances(): HasMany
    {
        return $this->hasMany(ProjectCompliance::class);
    }

    public function construction(): HasOne
    {
        return $this->hasOne(ProjectConstruction::class);
    }

    public function constructionMilestones(): HasMany
    {
        return $this->hasMany(ConstructionMilestone::class)->orderBy('sort_order');
    }

    public function siteUpdates(): HasMany
    {
        return $this->hasMany(SiteUpdate::class)->orderByDesc('update_date');
    }
}
