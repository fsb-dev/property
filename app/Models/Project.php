<?php

namespace App\Models;

use App\Enums\ProjectCategory;
use App\Enums\ProjectStatus;
use App\Enums\ProjectType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Project extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'type',
        'category',
        'location',
        'address',
        'description',
        'status',
        'total_floors',
        'total_units',
        'overall_progress',
        'handover_date',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'handover_date'    => 'date',
        'overall_progress' => 'decimal:2',
        'type'             => ProjectType::class,
        'category'         => ProjectCategory::class,
        'status'           => ProjectStatus::class,
    ];

    // ── Media collections ──────────────────────────────────────────

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('cover')
            ->singleFile()   // only one cover image
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('documents')
            ->acceptsMimeTypes([
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ]);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(400)->height(300)
            ->performOnCollections('images', 'cover');

        $this->addMediaConversion('medium')
            ->width(800)->height(600)
            ->performOnCollections('images', 'cover');
    }

    // ── Relationships ──────────────────────────────────────────────

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }
}
