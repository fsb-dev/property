<?php

namespace App\Http\Requests\Admin;

use App\Enums\ClientSource;
use App\Enums\ClientStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $clientId = $this->route('client')->id;

        return [
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'max:255', Rule::unique('clients', 'email')->ignore($clientId)],
            'phone'         => ['nullable', 'string', 'max:30'],
            'password'      => ['nullable', 'string', 'min:8'],
            'gender'        => ['nullable', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'nationality'   => ['nullable', 'string', 'max:100'],
            'nid'           => ['nullable', 'string', 'max:50'],
            'passport_no'   => ['nullable', 'string', 'max:50'],
            'occupation'    => ['nullable', 'string', 'max:150'],
            'address'       => ['nullable', 'string', 'max:500'],
            'source'        => ['nullable', new Enum(ClientSource::class)],
            'notes'         => ['nullable', 'string', 'max:2000'],
            'status'        => ['required', new Enum(ClientStatus::class)],

            'avatar'                 => ['nullable', 'image', 'max:2048'],
            'remove_avatar'          => ['nullable', 'boolean'],
            'new_kyc_documents'      => ['nullable', 'array'],
            'new_kyc_documents.*'    => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'remove_kyc_documents'   => ['nullable', 'array'],
            'remove_kyc_documents.*' => ['integer'],
        ];
    }
}
