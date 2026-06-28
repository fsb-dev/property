<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Developer extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'tenant_id',
        'name',
        'phone',
        'email',
        'address',
        'website',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml']);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
