<?php

namespace App\Models;

use App\Enums\ClientSource;
use App\Enums\ClientStatus;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Client extends Authenticatable implements HasMedia
{
    use Notifiable, InteractsWithMedia;

    protected $fillable = [
        'tenant_id', 'name', 'email', 'password',
        'phone', 'gender', 'date_of_birth', 'nationality',
        'nid', 'passport_no', 'occupation',
        'address', 'source', 'notes', 'status',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth'     => 'date',
        'password'          => 'hashed',
        'status'            => ClientStatus::class,
        'source'            => ClientSource::class,
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    // ── Media ──────────────────────────────────────────────────────────────────

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('kyc_documents')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'application/pdf']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(120)->height(120)
            ->performOnCollections('avatar');
    }
}
