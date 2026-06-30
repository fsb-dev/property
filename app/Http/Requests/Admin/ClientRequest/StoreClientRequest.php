<?php

namespace App\Http\Requests\Admin\ClientRequest;

use App\Enums\ClientCountry;
use App\Enums\ClientEmploymentType;
use App\Enums\ClientIndustry;
use App\Enums\ClientMaritalStatus;
use App\Enums\ClientSource;
use App\Enums\ClientStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $isDraft = $this->boolean('is_draft');
        $req     = $isDraft ? 'nullable' : 'required';

        return [
            // ── Meta ──────────────────────────────────────────────────────
            'is_draft' => ['nullable', 'boolean'],
            'status'   => ['required', new Enum(ClientStatus::class)],
            'source'   => ['nullable', new Enum(ClientSource::class)],
            'notes'    => ['nullable', 'string', 'max:2000'],
            'password' => ['nullable', 'string', 'min:8'],

            // ── Step 1: Personal ──────────────────────────────────────────
            'name'           => ['required', 'string', 'max:255'],
            'father_name'    => ['required', 'string', 'max:255'],
            'mother_name'    => ['required', 'string', 'max:255'],
            'gender'         => ['required', Rule::in(['male', 'female', 'other'])],
            'marital_status' => ['required', new Enum(ClientMaritalStatus::class)],
            'date_of_birth'  => ['required', 'date', 'before:today'],
            'nationality'    => ['required', 'string', 'max:100'],

            // ── Step 2: Contact ───────────────────────────────────────────
            'email'                   => ['required', 'email', 'max:255', 'unique:clients,email'],
            'phone'                   => ['required', 'string', 'max:30'],
            'alternate_phone'         => ['nullable', 'string', 'max:30'],
            'whatsapp'                => ['nullable', 'string', 'max:30'],
            'emergency_contact_name'  => ['required', 'string', 'max:255'],
            'emergency_contact_phone' => ['required', 'string', 'max:30'],

            // ── Step 3: ID / Passport ─────────────────────────────────────
            'nid'                      => ['nullable', 'string', 'max:50'],
            'birth_certificate_number' => ['nullable', 'string', 'max:50'],
            'passport_no'              => ['nullable', 'string', 'max:50'],
            'passport_expiry'          => ['nullable', 'date'],

            // ── Step 4: Address ───────────────────────────────────────────
            'address'           => ['nullable', 'string', 'max:500'],
            'country'           => ['required', new Enum(ClientCountry::class)],
            'city'              => ['required', 'string', 'max:100'],
            'state'             => ['required', 'string', 'max:100'],
            'postal_code'       => ['nullable', 'string', 'max:20'],
            'permanent_address' => ['nullable', 'string', 'max:500'],

            // ── Step 5: Employment ────────────────────────────────────────
            'occupation'      => ['nullable', 'string', 'max:150'],
            'employment_type' => ['nullable', new Enum(ClientEmploymentType::class)],
            'company'         => ['nullable', 'string', 'max:255'],
            'designation'     => ['nullable', 'string', 'max:150'],
            'industry'        => ['nullable', new Enum(ClientIndustry::class)],
            'office_address'  => ['nullable', 'string', 'max:500'],
            'tenure'          => ['nullable', 'string', 'max:100'],

            // ── Step 6: Income & Financials ───────────────────────────────
            'monthly_income' => ['nullable', 'string', 'max:50'],
            'annual_income'  => ['nullable', 'string', 'max:50'],
            'other_income'   => ['nullable', 'string', 'max:255'],
            'existing_loans' => ['nullable', 'string', 'max:100'],
            'bank_name'      => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:100'],

            // ── Step 7: Co-applicant ──────────────────────────────────────
            'coapplicant_name'                 => ['nullable', 'string', 'max:255'],
            'coapplicant_relationship'         => ['nullable', 'string', 'max:100'],
            'coapplicant_dob'                  => ['nullable', 'date'],
            'coapplicant_phone'                => ['nullable', 'string', 'max:30'],
            'coapplicant_email'                => ['nullable', 'email', 'max:255'],
            'coapplicant_nid'                  => ['nullable', 'string', 'max:50'],
            'coapplicant_occupation'           => ['nullable', 'string', 'max:150'],
            'coapplicant_monthly_income'       => ['nullable', 'string', 'max:50'],
            'coapplicant_annual_income'        => ['nullable', 'string', 'max:50'],
            'coapplicant_tin'                  => ['nullable', 'string', 'max:50'],
            'coapplicant_ownership_percentage' => ['nullable', 'string', 'max:20'],
            'coapplicant_address'              => ['nullable', 'string', 'max:500'],
            'coapplicant_signature'            => ['nullable', 'string', 'max:255'],

            // ── Step 8: Documents ─────────────────────────────────────────
            'new_kyc_documents'   => ['nullable', 'array'],
            'new_kyc_documents.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],

            // ── Step 9: Profile Photo ─────────────────────────────────────
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
