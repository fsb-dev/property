<?php

namespace App\Http\Requests\Admin\BookingRequest;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            // ── Buyer ─────────────────────────────────────────────────────
            'buyer_mode'        => ['required', 'in:existing,new'],
            'client_id'         => ['required_if:buyer_mode,existing', 'nullable', 'exists:clients,id'],
            'new_client_name'   => ['required_if:buyer_mode,new', 'nullable', 'string', 'max:255'],
            'new_client_phone'  => ['required_if:buyer_mode,new', 'nullable', 'string', 'max:30'],
            'new_client_email'  => ['nullable', 'email', 'max:255'],

            // ── Unit ──────────────────────────────────────────────────────
            'unit_id' => ['required', 'exists:units,id'],

            // ── Reservation details ─────────────────────────────────────
            'booking_date'   => ['required', 'date'],
            'reserved_until' => ['nullable', 'date', 'after_or_equal:booking_date'],
            'source'         => ['nullable', 'string', 'max:50'],
            'priority'       => ['nullable', 'string', 'max:20'],
            'notes'          => ['nullable', 'string', 'max:2000'],
            'sales_rep_id'   => ['nullable', 'exists:users,id'],

            // ── Pricing ───────────────────────────────────────────────────
            'discount_pct'  => ['nullable', 'numeric', 'min:0', 'max:100'],
            'price_agreed'  => ['required', 'numeric', 'min:0'],

            // ── Payment plan ──────────────────────────────────────────────
            'plan_type'          => ['nullable', 'string', 'max:50'],
            'down_payment'       => ['nullable', 'numeric', 'min:0'],
            'total_installments' => ['nullable', 'integer', 'min:1', 'max:120'],

            // ── Demo-only extras (documents / mortgage / approvals) ───────
            'meta' => ['nullable', 'array'],
        ];
    }
}
