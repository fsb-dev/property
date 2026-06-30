<?php

namespace App\Models;

use App\Enums\ClientCountry;
use App\Enums\ClientEmploymentType;
use App\Enums\ClientIndustry;
use App\Enums\ClientMaritalStatus;
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
        // Identity
        'tenant_id', 'name', 'email', 'password', 'status', 'source', 'notes',

        // Step 1: Personal
        'father_name', 'mother_name', 'gender', 'marital_status',
        'date_of_birth', 'nationality',

        // Step 2: Contact
        'phone', 'alternate_phone', 'whatsapp',
        'emergency_contact_name', 'emergency_contact_phone',

        // Step 3: ID / Passport
        'nid', 'birth_certificate_number', 'passport_no', 'passport_expiry',

        // Step 4: Address
        'address', 'country', 'city', 'state', 'postal_code', 'permanent_address',

        // Step 5: Employment
        'occupation', 'employment_type', 'company', 'designation',
        'industry', 'office_address', 'tenure',

        // Step 6: Income & Financials
        'monthly_income', 'annual_income', 'other_income',
        'existing_loans', 'bank_name', 'account_number',

        // Step 7: Co-applicant
        'coapplicant_name', 'coapplicant_relationship', 'coapplicant_dob',
        'coapplicant_phone', 'coapplicant_email', 'coapplicant_nid',
        'coapplicant_occupation', 'coapplicant_monthly_income', 'coapplicant_annual_income',
        'coapplicant_tin', 'coapplicant_ownership_percentage',
        'coapplicant_address', 'coapplicant_signature',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth'     => 'date',
        'passport_expiry'   => 'date',
        'coapplicant_dob'   => 'date',
        'password'          => 'hashed',
        'status'            => ClientStatus::class,
        'source'            => ClientSource::class,
        'marital_status'    => ClientMaritalStatus::class,
        'employment_type'   => ClientEmploymentType::class,
        'industry'          => ClientIndustry::class,
        'country'           => ClientCountry::class,
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
