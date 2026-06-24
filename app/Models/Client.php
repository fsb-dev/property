<?php

namespace App\Models;

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
        'phone', 'nid', 'address', 'status',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    // ── Media collections ──────────────────────────────────────────

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        // KYC docs: NID, passport, utility bill, etc.
        $this->addMediaCollection('kyc_documents')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'application/pdf']);

        // Booking/payment related documents sent to/from client
        $this->addMediaCollection('documents')
            ->acceptsMimeTypes(['application/pdf']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(120)->height(120)
            ->performOnCollections('avatar');
    }
}
