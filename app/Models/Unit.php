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
        'tenant_id', 'project_id', 'block_id', 'unit_number', 'block',
        'floor', 'sort_order', 'type', 'bedrooms', 'size_sqft',
        'view', 'price', 'status', 'handover_date',
    ];

    protected $casts = [
        'handover_date' => 'date',
        'price'         => 'decimal:2',
        'type'          => UnitType::class,
        'status'        => UnitStatus::class,
    ];

    // ── Media collections ──────────────────────────────────────────

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('floor_plan')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf']);

        $this->addMediaCollection('documents')
            ->acceptsMimeTypes(['application/pdf', 'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);
    }

    // ── Relationships ──────────────────────────────────────────────

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(ProjectBlock::class, 'block_id');
    }
}
