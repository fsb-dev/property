<?php

namespace App\Http\Requests\Admin\BookingRequest;

use App\Enums\BookingStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateBookingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'status'         => ['required', new Enum(BookingStatus::class)],
            'price_agreed'   => ['required', 'numeric', 'min:0'],
            'reserved_until' => ['nullable', 'date'],
            'sales_rep_id'   => ['nullable', 'exists:users,id'],
            'source'         => ['nullable', 'string', 'max:50'],
            'priority'       => ['nullable', 'string', 'max:20'],
            'discount_pct'   => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes'          => ['nullable', 'string', 'max:2000'],
        ];
    }
}
