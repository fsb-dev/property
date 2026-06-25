<?php

namespace App\Http\Requests\Admin;

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
        return [
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'max:255', 'unique:clients,email'],
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
            'new_kyc_documents'      => ['nullable', 'array'],
            'new_kyc_documents.*'    => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }
}
