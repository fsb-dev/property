<?php

namespace App\Services;

use App\Enums\ClientCountry;
use App\Enums\ClientEmploymentType;
use App\Enums\ClientIndustry;
use App\Enums\ClientMaritalStatus;
use App\Enums\ClientSource;
use App\Enums\ClientStatus;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ClientService
{
    public function paginate(Request $request): LengthAwarePaginator
    {
        return Client::query()
            ->when($request->search, fn($q) =>
                $q->where('name',  'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%")
            )
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->source, fn($q) => $q->where('source', $request->source))
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn(Client $c) => [
                'id'           => $c->id,
                'name'         => $c->name,
                'email'        => $c->email,
                'phone'        => $c->phone,
                'source'       => $c->source?->value,
                'source_label' => $c->source?->label(),
                'status'       => $c->status->value,
                'status_label' => $c->status->label(),
                'status_color' => $c->status->color(),
                'avatar'       => $c->getFirstMediaUrl('avatar', 'thumb') ?: $c->getFirstMediaUrl('avatar'),
                'created_at'   => $c->created_at->format('d M Y'),
            ]);
    }

    public function stats(): array
    {
        return [
            'total'       => Client::count(),
            'active'      => Client::where('status', ClientStatus::Active)->count(),
            'inactive'    => Client::where('status', ClientStatus::Inactive)->count(),
            'blacklisted' => Client::where('status', ClientStatus::Blacklisted)->count(),
        ];
    }

    public function enums(): array
    {
        return [
            'statuses' => collect(ClientStatus::cases())->map(fn($e) => [
                'value' => $e->value,
                'label' => $e->label(),
                'color' => $e->color(),
            ]),
            'sources' => collect(ClientSource::cases())->map(fn($e) => [
                'value' => $e->value,
                'label' => $e->label(),
            ]),
            'genders' => [
                ['value' => 'male',   'label' => 'Male'],
                ['value' => 'female', 'label' => 'Female'],
                ['value' => 'other',  'label' => 'Other'],
            ],
            'marital_statuses' => collect(ClientMaritalStatus::cases())->map(fn($e) => [
                'value' => $e->value,
                'label' => $e->label(),
            ]),
            'employment_types' => collect(ClientEmploymentType::cases())->map(fn($e) => [
                'value' => $e->value,
                'label' => $e->label(),
            ]),
            'industries' => collect(ClientIndustry::cases())->map(fn($e) => [
                'value' => $e->value,
                'label' => $e->label(),
            ]),
            'countries' => collect(ClientCountry::cases())->map(fn($e) => [
                'value' => $e->value,
                'label' => $e->label(),
            ]),
        ];
    }

    public function forEdit(Client $client): array
    {
        return [
            'id'     => $client->id,
            'status' => $client->status->value,
            'source' => $client->source?->value,
            'notes'  => $client->notes,

            // Step 1: Personal
            'name'           => $client->name,
            'father_name'    => $client->father_name,
            'mother_name'    => $client->mother_name,
            'gender'         => $client->gender,
            'marital_status' => $client->marital_status?->value,
            'date_of_birth'  => $client->date_of_birth?->format('Y-m-d'),
            'nationality'    => $client->nationality,

            // Step 2: Contact
            'email'                   => $client->email,
            'phone'                   => $client->phone,
            'alternate_phone'         => $client->alternate_phone,
            'whatsapp'                => $client->whatsapp,
            'emergency_contact_name'  => $client->emergency_contact_name,
            'emergency_contact_phone' => $client->emergency_contact_phone,

            // Step 3: ID / Passport
            'nid'                      => $client->nid,
            'birth_certificate_number' => $client->birth_certificate_number,
            'passport_no'              => $client->passport_no,
            'passport_expiry'          => $client->passport_expiry?->format('Y-m-d'),

            // Step 4: Address
            'address'           => $client->address,
            'country'           => $client->country?->value,
            'city'              => $client->city,
            'state'             => $client->state,
            'postal_code'       => $client->postal_code,
            'permanent_address' => $client->permanent_address,

            // Step 5: Employment
            'occupation'      => $client->occupation,
            'employment_type' => $client->employment_type?->value,
            'company'         => $client->company,
            'designation'     => $client->designation,
            'industry'        => $client->industry?->value,
            'office_address'  => $client->office_address,
            'tenure'          => $client->tenure,

            // Step 6: Income & Financials
            'monthly_income'  => $client->monthly_income,
            'annual_income'   => $client->annual_income,
            'other_income'    => $client->other_income,
            'existing_loans'  => $client->existing_loans,
            'bank_name'       => $client->bank_name,
            'account_number'  => $client->account_number,

            // Step 7: Co-applicant
            'coapplicant_name'                => $client->coapplicant_name,
            'coapplicant_relationship'        => $client->coapplicant_relationship,
            'coapplicant_dob'                 => $client->coapplicant_dob?->format('Y-m-d'),
            'coapplicant_phone'               => $client->coapplicant_phone,
            'coapplicant_email'               => $client->coapplicant_email,
            'coapplicant_nid'                 => $client->coapplicant_nid,
            'coapplicant_occupation'          => $client->coapplicant_occupation,
            'coapplicant_monthly_income'      => $client->coapplicant_monthly_income,
            'coapplicant_annual_income'       => $client->coapplicant_annual_income,
            'coapplicant_tin'                 => $client->coapplicant_tin,
            'coapplicant_ownership_percentage'=> $client->coapplicant_ownership_percentage,
            'coapplicant_address'             => $client->coapplicant_address,
            'coapplicant_signature'           => $client->coapplicant_signature,

            // Media
            'avatar'        => $client->getFirstMediaUrl('avatar'),
            'kyc_documents' => $client->getMedia('kyc_documents')->map(fn($m) => [
                'id'   => $m->id,
                'url'  => $m->getUrl(),
                'name' => $m->file_name,
                'size' => $m->size,
                'mime' => $m->mime_type,
            ])->toArray(),
        ];
    }

    public function forShow(Client $client): array
    {
        return [
            ...$this->forEdit($client),
            'source_label'            => $client->source?->label(),
            'status_label'            => $client->status->label(),
            'status_color'            => $client->status->color(),
            'created_at'              => $client->created_at->format('d M Y'),
            'date_of_birth_formatted' => $client->date_of_birth?->format('d M Y'),
            'passport_expiry_formatted' => $client->passport_expiry?->format('d M Y'),
            'coapplicant_dob_formatted' => $client->coapplicant_dob?->format('d M Y'),
        ];
    }

    public function create(array $data): Client
    {
        $mediaKeys  = ['avatar', 'new_kyc_documents', 'remove_kyc_documents'];
        $media      = Arr::only($data, $mediaKeys);
        $attributes = Arr::except($data, [...$mediaKeys, 'is_draft']);

        if (empty($attributes['password'])) {
            $attributes['password'] = "12345678";
        }

        $client = Client::create($attributes);
        $this->attachMedia($client, $media);

        return $client;
    }

    public function update(Client $client, array $data): Client
    {
        $mediaKeys  = ['avatar', 'remove_avatar', 'new_kyc_documents', 'remove_kyc_documents'];
        $media      = Arr::only($data, $mediaKeys);
        $attributes = Arr::except($data, [...$mediaKeys, 'is_draft']);

        if (empty($attributes['password'])) {
            unset($attributes['password']);
        }

        $client->update($attributes);
        $this->attachMedia($client, $media);

        return $client->fresh();
    }

    public function delete(Client $client): string
    {
        $name = $client->name;
        $client->delete();
        return $name;
    }

    private function attachMedia(Client $client, array $data): void
    {
        if (!empty($data['avatar'])) {
            $client->clearMediaCollection('avatar');
            $client->addMedia($data['avatar'])->toMediaCollection('avatar');
        } elseif ($data['remove_avatar'] ?? false) {
            $client->clearMediaCollection('avatar');
        }

        foreach ($data['new_kyc_documents'] ?? [] as $file) {
            $client->addMedia($file)->toMediaCollection('kyc_documents');
        }
        foreach ($data['remove_kyc_documents'] ?? [] as $mediaId) {
            $client->deleteMedia((int) $mediaId);
        }
    }
}
